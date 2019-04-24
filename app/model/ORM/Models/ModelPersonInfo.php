<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\DateTime;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read string email
 * @property-read string phone
 * @property-read string phone_parent_m
 * @property-read string phone_parent_d
 * @property-read string born_id
 */
class ModelPersonInfo extends AbstractModelSingle {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
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

