<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readstring email
 * @property-readstring phone
 * @property-readstring phone_parent_m
 * @property-readstring phone_parent_d
 * @property-readstring born_id
 */
class ModelPersonInfo extends AbstractModelSingle {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

}

