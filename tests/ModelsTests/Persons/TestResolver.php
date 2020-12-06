<?php

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\ORM\Models\ModelPerson;
use FKSDB\Persons\IModifiabilityResolver;
use FKSDB\Persons\IVisibilityResolver;
use FKSDB\Persons\ReferencedPersonHandler;

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

