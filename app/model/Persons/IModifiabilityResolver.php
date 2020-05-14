<?php

namespace Persons;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IModifiabilityResolver {

    /**
     * @param ModelPerson $person
     * @return bool
     */
    public function isModifiable(ModelPerson $person): bool;

    /**
     * @param ModelPerson $person
     * @return string
     */
    public function getResolutionMode(ModelPerson $person): string;
}
