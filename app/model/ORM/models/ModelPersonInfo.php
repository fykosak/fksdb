<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string email
 */
class ModelPersonInfo extends AbstractModelSingle {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

}

