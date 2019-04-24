<?php

namespace Persons;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IVisibilityResolver {

    /**
     * @param \FKSDB\ORM\Models\ModelPerson $person
     * @return mixed
     */
    public function isVisible(ModelPerson $person);
}
