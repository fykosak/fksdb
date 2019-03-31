<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\DateTime;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string email
 * @property string phone
 * @property string phone_parent_m
 * @property string phone_parent_d
 * @property string born_id
 */
class ModelPersonInfo extends AbstractModelSingle {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

    public function update($data) {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new DateTime();
            } else if ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::update($data);
    }

}

