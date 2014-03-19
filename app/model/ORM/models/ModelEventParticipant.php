<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelEventParticipant extends AbstractModelSingle {

//    private $person = false;

    public function getPerson() {
//        if ($this->person === false) {
//            $row = $this->ref(DbNames::TAB_PERSON, 'person_id');
//            $this->person = $row ? ModelPerson::createFromTableRow($row) : null;
//        }
//
//        return $this->person;
        $this->person_id; // stupid touch
        $row = $this->ref(DbNames::TAB_PERSON, 'person_id');
        return $row ? ModelPerson::createFromTableRow($row) : null;
    }

    public function __toString() {
        return $this->getPerson()->getFullname();
    }

}

?>
