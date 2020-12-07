<?php

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Model\ORM\Models\ModelPerson;
use FKSDB\Model\Persons\IModifiabilityResolver;
use FKSDB\Model\Persons\IVisibilityResolver;
use FKSDB\Model\Persons\ReferencedPersonHandler;

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

