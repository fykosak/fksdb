<?php

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

    public function __construct(Session $sessionHandler, ServiceLogin $loginService, YearCalculator $yearCalculator) {
        parent::__construct($sessionHandler);
        $this->loginService = $loginService;
        $this->yearCalculator = $yearCalculator;
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

