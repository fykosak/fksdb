<?php

namespace Persons;

use ModelPerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IModifiabilityResolver {

    public function isModifiable(ModelPerson $person);

    public function getResolutionMode(ModelPerson $person);
}
