<?php

namespace FKSDB\Authentication;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Authentication\SSO\GlobalSession;
use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\YearCalculator;
use Nette\Application\AbortException;
use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Http\Request;
use Nette\Http\Session;
use Nette\Http\UserStorage;
use Nette\Security\Identity;
use Nette\Security\IIdentity;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @see http://forum.nette.org/cs/9574-jak-rozsirit-userstorage
 * @property IIdentity $identity
 */
class LoginUserStorage extends UserStorage {
    /** @const HTTP GET parameter holding control information for the SSO */

    public const PARAM_SSO = ModelAuthToken::TYPE_SSO;

    /** @const Value meaning the user is not centally authneticated. */
    public const SSO_AUTHENTICATED = 'a';

    /** @const Value meaning the user is not centally authneticated. */
    public const SSO_UNAUTHENTICATED = 'ua';

    private ServiceLogin $serviceLogin;

    private YearCalculator $yearCalculator;

    private GlobalSession $globalSession;

    private Application $application;

    /** @var IPresenter */
    private $presenter;

    private Request $request;

    public function __construct(
        Session $sessionHandler,
        ServiceLogin $loginService,
        YearCalculator $yearCalculator,
        GlobalSession $globalSession,
        Application $application,
        Request $request
    ) {
        parent::__construct($sessionHandler);
        $this->serviceLogin = $loginService;
        $this->yearCalculator = $yearCalculator;
        $this->globalSession = $globalSession;
        $this->application = $application;
        $this->request = $request;
    }

    public function getPresenter(): IPresenter {
        if ($this->application->getPresenter()) {
            return $this->application->getPresenter();
        } else {
            return $this->presenter;
        }
    }

    /**
     * @param IPresenter $presenter
     * @internal Used internally or for testing purposes only.
     *
     */
    public function setPresenter(IPresenter $presenter): void {
        $this->presenter = $presenter;
    }

    /**
     * @param mixed $state
     * @return static
     */
    public function setAuthenticated($state): self {
        parent::setAuthenticated($state);
        if ($state) {
            $uid = parent::getIdentity()->getId();
            $this->globalSession[GlobalSession::UID] = $uid;
        } else {
            unset($this->globalSession[GlobalSession::UID]);
        }
        return $this;
    }

    /**
     * @return bool
     * @throws AbortException
     */
    public function isAuthenticated(): bool {
        $local = parent::isAuthenticated();
        $global = isset($this->globalSession[GlobalSession::UID]) ? $this->globalSession[GlobalSession::UID] : null;

        if ($global) {
            // update identity
            $identity = new Identity($global);
            parent::setIdentity($identity);

            /* As we return true, we must ensure local login will be properly set,
             * hence AuthenticatedPresenter sets up login status neglecting actual login status (i.e. overwrites).
             * In AuthenticationPresenter, we must manually check global session whether UID is set.
             */
            return $local;
        } else {
            /* Commenting this line out fixes bug #9,
             * probably is not needed anymore.
             */
            //parent::setAuthenticated(false);
            /** @var AuthenticatedPresenter $presenter */
            $presenter = $this->getPresenter();
            $ssoData = $presenter->getParameter(self::PARAM_SSO);

            /* If this is the first try, we redirect to the central login page,
             * otherwise we avoid redirection loop by checking PARAM_SSO and
             * redirection to the login page will be done in the startup method.
             */
            if (!$ssoData && $presenter instanceof AuthenticatedPresenter) {
                $allowedNonlogin = ($presenter->getAllowedAuthMethods() &
                    (AuthenticatedPresenter::AUTH_ALLOW_HTTP | AuthenticatedPresenter::AUTH_ALLOW_GITHUB));
                if ($presenter->requiresLogin() && !$allowedNonlogin) {
                    $params = [
                        'backlink' => (string)$this->request->getUrl(),
                        AuthenticationPresenter::PARAM_FLAG => AuthenticationPresenter::FLAG_SSO_PROBE,
                        AuthenticationPresenter::PARAM_REASON => AuthenticationPresenter::REASON_AUTH,
                    ];

                    $presenter->redirect(':Core:Authentication:login', $params);
                }
            }
            return false;
        }
    }

    public function setIdentity(?IIdentity $identity = null): self {
        $this->identity = $identity;
        if ($identity instanceof ModelLogin) {
            $identity = new Identity($identity->getId());
        }
        return parent::setIdentity($identity);
    }

    public function getIdentity(): ?ModelLogin {
        $local = parent::getIdentity();
        $global = isset($this->globalSession[GlobalSession::UID]) ? $this->globalSession[GlobalSession::UID] : null;
        /*
         * Note that case when $global == true && $local != true should be resolved,
         * i.e. update local session from global. However, this is already done
         * int isAuthenticated method. Thus we can omit this case here.
         */
        if (!$local || !$global) {
            return null;
        }

        // Find login
        /** @var ModelLogin $login */
        $login = $this->serviceLogin->findByPrimary($local->getId());

        if (!$login) {
            return null;
        }
        $login->person_id; // stupid... touch the field in order to have it loaded via ActiveRow
        $login->injectYearCalculator($this->yearCalculator);
        return $login;
    }

}
