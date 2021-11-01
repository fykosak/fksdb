<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\ReferencedHandler;
use FKSDB\Models\Persons\VisibilityResolver;

class TestResolver implements VisibilityResolver, ModifiabilityResolver
{

    public function getResolutionMode(?ModelPerson $person): string
    {
        return ReferencedHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?ModelPerson $person): bool
    {
        return true;
    }

    public function isVisible(?ModelPerson $person): bool
    {
        return true;
    }
}
