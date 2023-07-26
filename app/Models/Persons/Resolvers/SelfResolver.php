<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\Security\User;
use Nette\SmartObject;

class SelfResolver implements Resolver
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

    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        if (!$person) {
            return ResolutionMode::tryFrom(ResolutionMode::EXCEPTION);
        }
        return $this->isSelf($person)
            ? ResolutionMode::tryFrom(ResolutionMode::OVERWRITE)
            : ResolutionMode::tryFrom(ResolutionMode::EXCEPTION);
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
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        $loggedPerson = $login->person;
        return $loggedPerson && $loggedPerson->person_id == $person->person_id;
    }
}
