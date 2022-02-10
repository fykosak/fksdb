<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\Security\User;
use Nette\SmartObject;

class SelfResolver implements VisibilityResolver, ModifiabilityResolver
{
    use SmartObject;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function isVisible(?ModelPerson $person): bool
    {
        return !$person || $this->isSelf($person);
    }

    public function getResolutionMode(?ModelPerson $person): string
    {
        if (!$person) {
            return ReferencedHandler::RESOLUTION_EXCEPTION;
        }
        return $this->isSelf($person) ? ReferencedHandler::RESOLUTION_OVERWRITE
            : ReferencedHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?ModelPerson $person): bool
    {
        return !$person || $this->isSelf($person);
    }

    protected function isSelf(ModelPerson $person): bool
    {
        if (!$this->user->isLoggedIn()) {
            return false;
        }
        /** @var ModelLogin $login */
        $login = $this->user->getIdentity();
        $loggedPerson = $login->getPerson();
        return $loggedPerson && $loggedPerson->person_id == $person->person_id;
    }
}
