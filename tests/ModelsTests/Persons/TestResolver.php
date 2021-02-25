<?php

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\VisibilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandler;

class TestResolver implements VisibilityResolver, ModifiabilityResolver {

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

