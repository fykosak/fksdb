<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelContestant extends AbstractModelSingle {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

}
