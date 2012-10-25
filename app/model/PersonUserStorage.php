<?php

use Nette\Http\UserStorage;
use Nette\Http\Session;
use Nette\Security\IIdentity;
use Nette\Security\Identity;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @see http://forum.nette.org/cs/9574-jak-rozsirit-userstorage
 */
class PersonUserStorage extends UserStorage {

    /** @var ServicePerson */
    private $personService;

    public function __construct(Session $sessionHandler, ServicePerson $personService) {
        parent::__construct($sessionHandler);
        $this->personService = $personService;
    }

    /**
     * @param IIdentity
     * @return PersonUserStorage
     */
    public function setIdentity(IIdentity $identity = NULL) {
        $this->identity = $identity;
        if ($identity instanceof ModelPerson) {
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
        // Find person
        return $this->personService->findByPrimary($identity->getId());
    }

}

