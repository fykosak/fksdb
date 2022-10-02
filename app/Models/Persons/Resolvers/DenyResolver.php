<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\SmartObject;

class DenyResolver implements Resolver
{
    use SmartObject;

    public function isVisible(?PersonModel $person): bool
    {
        return false;
    }

    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        return ResolutionMode::tryFrom(ResolutionMode::EXCEPTION);
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return false;
    }
}
