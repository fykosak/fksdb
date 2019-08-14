<?php

namespace Authentication;

use AuthenticatedPresenter;
use Authentication\SSO\GlobalSession;
use AuthenticationPresenter;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\YearCalculator;
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

    const PARAM_SSO = 'sso';

    /** @const Value meaning the user is not centally authneticated. */
    const SSO_AUTHENTICATED = 'a';

    /** @const Value meaning the user is not centally authneticated. */
    const SSO_UNAUTHENTICATED = 'ua';

    /** @var ServiceLogin */
    private $loginService;

    /** @var YearCalculator */
    private $yearCalculator;

    /**
     * @var GlobalSession
     */
    private $globalSession;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var IPresenter
     */
    private $presenter;

    /**
     * @var Request
     */
    private $request;

    /**
     * LoginUserStorage constructor.
     * @param Session $sessionHandler
     * @param ServiceLogin $loginService
     * @param \FKSDB\YearCalculator $yearCalculator
     * @param GlobalSession $globalSession
     * @param Application $application
     * @param Request $request
     */
    function __construct(Session $sessionHandler, ServiceLogin $loginService, YearCalculator $yearCalculator, GlobalSession $globalSession, Application $application, Request $request) {
        parent::__construct($sessionHandler);
        $this->loginService = $loginService;
        $this->yearCalculator = $yearCalculator;
        $this->globalSession = $globalSession;
        $this->application = $application;
        $this->request = $request;
    }

    /**
     * @return IPresenter
     */
    public function getPresenter() {
        if ($this->application->getPresenter()) {
            return $this->application->getPresenter();
        } else {
            return $this->presenter;
        }
    }

    /**
     * @internal Used internally or for testing purposes only.
     *
     * @param IPresenter $presenter
     */
    public function setPresenter(IPresenter $presenter) {
        $this->presenter = $presenter;
    }

    /**
     * @param $state
     * @return UserStorage|void
     */
    public function setAuthenticated($state) {
        parent::setAuthenticated($state);
        if ($state) {
            $uid = parent::getIdentity()->getId();
            $this->globalSession[GlobalSession::UID] = $uid;
        } else {
            unset($this->globalSession[GlobalSession::UID]);
        }
    }

    /**
     * @return bool
     * @throws \Nette\Application\AbortException
     */
    public function isAuthenticated() {
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
            /**
             * @var AuthenticatedPresenter $presenter
             */
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
                    $params = array(
                        'backlink' => (string)$this->request->getUrl(),
                        AuthenticationPresenter::PARAM_FLAG => AuthenticationPresenter::FLAG_SSO_PROBE,
                        AuthenticationPresenter::PARAM_REASON => AuthenticationPresenter::REASON_AUTH,
                    );

                    $presenter->redirect(':Authentication:login', $params);
                }
            }
            return false;
        }
    }

    /**
     * @param IIdentity|NULL $identity
     * @return UserStorage
     */
    public function setIdentity(IIdentity $identity = NULL) {
        $this->identity = $identity;
        if ($identity instanceof ModelLogin) {
            $identity = new Identity($identity->getID());
        }
        return parent::setIdentity($identity);
    }

    /**
     * @return ModelLogin|NULL
     */
    public function getIdentity() {
        $local = parent::getIdentity();
        $global = isset($this->globalSession[GlobalSession::UID]) ? $this->globalSession[GlobalSession::UID] : null;
        /*
         * Note that case when $global == true && $local != true should be resolved,
         * i.e. update local session from global. However, this is already done
         * int isAuthenticated method. Thus we can omit this case here.
         */
        if (!$local || !$global) {
            return NULL;
        }

        // Find login
        $row = $this->loginService->findByPrimary($local->getId());

        if (!$row) {
            return null;
        }
        $login = ModelLogin::createFromActiveRow($row);
        $login->person_id; // stupid... touch the field in order to have it loaded via ActiveRow
        $login->injectYearCalculator($this->yearCalculator);
        return $login;
    }

}
