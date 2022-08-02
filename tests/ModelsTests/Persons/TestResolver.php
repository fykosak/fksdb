<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\ReferencedHandler;
use FKSDB\Models\Persons\VisibilityResolver;

class TestResolver implements VisibilityResolver, ModifiabilityResolver
{

    public function getResolutionMode(?PersonModel $person): string
    {
        return ReferencedHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return true;
    }

    public function isVisible(?PersonModel $person): bool
    {
        return true;
    }
}
