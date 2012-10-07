<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @see http://forum.nette.org/cs/9574-jak-rozsirit-userstorage
 */
class PersonUserStorage extends NUserStorage {

    /** @var ServicePerson */
    private $personService;

    public function __construct(NSession $sessionHandler, ServicePerson $personService) {
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
            $identity = new NIdentity($identity->getID());
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

