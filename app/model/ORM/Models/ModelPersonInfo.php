<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string email
 * @property string phone
 * @property string phone_parent_m
 * @property string phone_parent_d
 */
class ModelPersonInfo extends AbstractModelSingle {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

}

