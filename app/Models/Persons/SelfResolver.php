<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
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

    public function isVisible(?PersonModel $person): bool
    {
        return !$person || $this->isSelf($person);
    }

    public function getResolutionMode(?PersonModel $person): string
    {
        if (!$person) {
            return ReferencedHandler::RESOLUTION_EXCEPTION;
        }
        return $this->isSelf($person) ? ReferencedHandler::RESOLUTION_OVERWRITE
            : ReferencedHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return !$person || $this->isSelf($person);
    }

    protected function isSelf(PersonModel $person): bool
    {
        if (!$this->user->isLoggedIn()) {
            return false;
        }
        /** @var LoginModel $login */
        $login = $this->user->getIdentity();
        $loggedPerson = $login->person;
        return $loggedPerson && $loggedPerson->person_id == $person->person_id;
    }
}
