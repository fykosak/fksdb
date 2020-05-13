<?php

namespace Persons;

use FKSDB\ORM\Models\ModelPerson;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SelfResolver implements IVisibilityResolver, IModifiabilityResolver {
    use SmartObject;
    /**
     * @var User
     */
    private $user;

    /**
     * SelfResolver constructor.
     * @param User $user
     */
    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * @param ModelPerson $person
     * @return bool
     */
    public function isVisible(ModelPerson $person) {
        return $person->isNew() || $this->isSelf($person);
    }

    /**
     * @param ModelPerson $person
     * @return mixed|string
     */
    public function getResolutionMode(ModelPerson $person) {
        return $this->isSelf($person) ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    /**
     * @param ModelPerson $person
     * @return bool|mixed
     */
    public function isModifiable(ModelPerson $person) {
        return $person->isNew() || $this->isSelf($person);
    }

    /**
     * @param ModelPerson $person
     * @return bool
     */
    protected function isSelf(ModelPerson $person) {
        if (!$this->user->isLoggedIn()) {
            return false;
        }

        $loggedPerson = $this->user->getIdentity()->getPerson();
        return $loggedPerson && $loggedPerson->person_id == $person->person_id;
    }

}
