<?php

namespace Persons;

use FKSDB\ORM\ModelPerson;
use Nette\Object;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DenyResolver extends Object implements IVisibilityResolver, IModifiabilityResolver {

    public function isVisible(ModelPerson $person) {
        return false;
    }

    public function getResolutionMode(ModelPerson $person) {
        return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person) {
        return false;
    }

}
