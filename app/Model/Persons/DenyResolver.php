<?php

namespace FKSDB\Model\Persons;

use FKSDB\Model\ORM\Models\ModelPerson;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DenyResolver implements IVisibilityResolver, IModifiabilityResolver {
    use SmartObject;

    public function isVisible(ModelPerson $person): bool {
        return false;
    }

    public function getResolutionMode(ModelPerson $person): string {
        return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person): bool {
        return false;
    }
}