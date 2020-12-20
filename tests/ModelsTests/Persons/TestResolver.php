<?php

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\Persons\IModifiabilityResolver;
use FKSDB\Models\Persons\IVisibilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandler;

class TestResolver implements IVisibilityResolver, IModifiabilityResolver {

    public function getResolutionMode(ModelPerson $person): string {
        return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person): bool {
        return true;
    }

    public function isVisible(ModelPerson $person): bool {
        return true;
    }

}

