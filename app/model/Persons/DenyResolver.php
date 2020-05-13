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
    public function isVisible(ModelPerson $person) {
        return false;
    }

    /**
     * @param ModelPerson $person
     * @return mixed|string
     */
    public function getResolutionMode(ModelPerson $person) {
        return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    /**
     * @param ModelPerson $person
     * @return bool|mixed
     */
    public function isModifiable(ModelPerson $person) {
        return false;
    }

}
