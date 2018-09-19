<?php

use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelContestant extends AbstractModelSingle implements IResource {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        //$data = $this->getTable()->getConnection()->table(DbNames::TAB_PERSON)->where('person_id = ?', $this->person_id)->fetch();
        $data = $this->person;
        return ModelPerson::createFromTableRow($data);
    }

    /**
     * @return ModelContest
     */
    public function getContest() {
        $data = $this->contest;
        return ModelContest::createFromTableRow($data);
    }

    public function getResourceId() {
        return 'contestant';
    }

}
