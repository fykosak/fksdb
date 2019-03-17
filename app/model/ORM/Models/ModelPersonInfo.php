<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string email
 */
class ModelPersonInfo extends AbstractModelSingle {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

}

