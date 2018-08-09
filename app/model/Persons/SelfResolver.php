<?php

namespace Persons;

use ModelPerson;
use Nette\Object;
use Nette\Security\User;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SelfResolver extends Object implements IResolver {

    /**
     * @var User
     */
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function isVisible(ModelPerson $person) {
        return $person->isNew() || $this->isSelf($person);
    }

    public function getResolutionMode(ModelPerson $person) {
        return $this->isSelf($person) ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person) {
        return $person->isNew() || $this->isSelf($person);
    }

    protected function isSelf(ModelPerson $person) {
        if (!$this->user->isLoggedIn()) {
            return false;
        }

        $loggedPerson = $this->user->getIdentity()->getPerson();
        return $loggedPerson && $loggedPerson->person_id == $person->person_id;
    }

}
