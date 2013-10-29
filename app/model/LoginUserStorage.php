<?php

use Authentication\SSO\GlobalSession;
use Nette\Application\Application;
use Nette\Diagnostics\Debugger;
use Nette\Http\Request;
use Nette\Http\Session;
use Nette\Http\UserStorage;
use Nette\Security\Identity;
use Nette\Security\IIdentity;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @see http://forum.nette.org/cs/9574-jak-rozsirit-userstorage
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
     * @var Request
     */
    private $request;

    function __construct(Session $sessionHandler, ServiceLogin $loginService, YearCalculator $yearCalculator, GlobalSession $globalSession, Application $application, Request $request) {
        parent::__construct($sessionHandler);
        $this->loginService = $loginService;
        $this->yearCalculator = $yearCalculator;
        $this->globalSession = $globalSession;
        $this->application = $application;
        $this->request = $request;
    }

    public function setAuthenticated($state) {
        parent::setAuthenticated($state);
        if ($state) {
            $uid = parent::getIdentity()->getId();
            $this->globalSession[GlobalSession::UID] = $uid;
        } else {
            unset($this->globalSession[GlobalSession::UID]);
        }
    }

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
            parent::setAuthenticated(false); // somehow session contains authenticated flag

            $presenter = $this->application->getPresenter();
            $ssoData = $presenter->getParameter(self::PARAM_SSO);

            /* If this is the first try, we redirect to the central login page,
             * otherwise we avoid redirection loop by checking PARAM_SSO and
             * redirection to the login page will be done in the startup method.
             */
            if (!$ssoData) {
                $params = array(
                    'backlink' => (string) $this->request->getUrl(),
                    'flag' => AuthenticationPresenter::FLAG_SSO_LOGIN,
                    AuthenticationPresenter::PARAM_REASON => AuthenticationPresenter::REASON_AUTH,
                );

                if ($presenter instanceof AuthenticatedPresenter) {
                    $presenter->redirect(':Authentication:login', $params);
                }
            }
            return false;
        }
    }

    /**
     * @param IIdentity
     * @return LoginUserStorage
     */
    public function setIdentity(IIdentity $identity = NULL) {
        $this->identity = $identity;
        if ($identity instanceof ModelLogin) {
            $identity = new Identity($identity->getID());
        }
        return parent::setIdentity($identity);
    }

    /**
     * @return IIdentity|NULL
     */
    public function getIdentity() {
        $identity = parent::getIdentity();
        if (!$identity) {
            return NULL;
        }

        // Find login
        $login = $this->loginService->findByPrimary($identity->getId());
        $login->person_id; // stupid... touch the field in order to have it loaded via ActiveRow
        $login->injectYearCalculator($this->yearCalculator);
        return $login;
    }

}

