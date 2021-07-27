<?php

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\SmartObject;

class DenyResolver implements VisibilityResolver, ModifiabilityResolver {

    use SmartObject;

    public function isVisible(?ModelPerson $person): bool {
        return false;
    }

    public function getResolutionMode(?ModelPerson $person): string {
        return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?ModelPerson $person): bool {
        return false;
    }
}
