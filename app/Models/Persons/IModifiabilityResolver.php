<?php

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\ModelPerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IModifiabilityResolver {

    public function isModifiable(ModelPerson $person): bool;

    public function getResolutionMode(ModelPerson $person): string;
}
