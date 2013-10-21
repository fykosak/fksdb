<?php

use Authentication\SSO\GlobalSession;
use Nette\Application\Application;
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

            // if we're not locally logged, AuthenticatedPresetner would catch this
            return $local;
        } else {
            $presenter = $this->application->getPresenter();
            $params = array(
                'backlink' => (string) $this->request->getUrl(),
                'flag' => AuthenticationPresenter::FLAG_SSO,
            );

            parent::setAuthenticated(false); // somehow session contains authenticated flag

            if ($presenter instanceof AuthenticatedPresenter) {
                $presenter->redirect(':Authentication:login', $params);
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

