<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\SmartObject;

class SelfPersonResolver implements Resolver
{
    use SmartObject;

    private ?PersonModel $loggedPerson;

    public function __construct(?PersonModel $loggedPerson)
    {
        $this->loggedPerson = $loggedPerson;
    }

    public function isVisible(?PersonModel $person): bool
    {
        return !$person || $this->isSelf($person);
    }

    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        if (!$person) {
            return ResolutionMode::from(ResolutionMode::EXCEPTION);
        }
        return $this->isSelf($person)
            ? ResolutionMode::from(ResolutionMode::OVERWRITE)
            : ResolutionMode::from(ResolutionMode::EXCEPTION);
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return !$person || $this->isSelf($person);
    }

    protected function isSelf(PersonModel $person): bool
    {
        if (!isset($this->loggedPerson)) {
            return false;
        }
        return $this->loggedPerson->person_id == $person->person_id;
    }
}
