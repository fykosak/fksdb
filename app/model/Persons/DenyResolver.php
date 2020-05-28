<?php

namespace Persons;

use FKSDB\ORM\Models\ModelPerson;
use Nette\SmartObject;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DenyResolver implements IVisibilityResolver, IModifiabilityResolver {
    use SmartObject;

    /**
     * @param ModelPerson $person
     * @return bool
     */
    public function isVisible(ModelPerson $person): bool {
        return false;
    }

    /**
     * @param ModelPerson $person
     * @return string
     */
    public function getResolutionMode(ModelPerson $person): string {
        return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    /**
     * @param ModelPerson $person
     * @return bool
     */
    public function isModifiable(ModelPerson $person): bool {
        return false;
    }

}
