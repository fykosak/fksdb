<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\PersonModel;
use Nette\SmartObject;

class DenyResolver implements VisibilityResolver, ModifiabilityResolver
{
    use SmartObject;

    public function isVisible(?PersonModel $person): bool
    {
        return false;
    }

    public function getResolutionMode(?PersonModel $person): string
    {
        return ReferencedHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return false;
    }
}
