<?php

namespace Persons;

use FKSDB\ORM\Models\ModelLogin;
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

    public function isVisible(ModelPerson $person): bool {
        return $person->isNew() || $this->isSelf($person);
    }

    public function getResolutionMode(ModelPerson $person): string {
        return $this->isSelf($person) ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person): bool {
        return $person->isNew() || $this->isSelf($person);
    }

    protected function isSelf(ModelPerson $person): bool {
        if (!$this->user->isLoggedIn()) {
            return false;
        }
        /** @var ModelLogin $login */
        $login = $this->user->getIdentity();
        $loggedPerson = $login->getPerson();
        return $loggedPerson && $loggedPerson->person_id == $person->person_id;
    }

}
