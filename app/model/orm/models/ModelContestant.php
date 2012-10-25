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
        $data = $this->getTable()->getConnection()->table(DbNames::TAB_PERSON)->where('person_id = ?', $this->person_id)->fetch();
        return ModelPerson::createFromTableRow($data);
    }

}
