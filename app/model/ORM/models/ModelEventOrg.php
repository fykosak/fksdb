<?php

use Nette\InvalidStateException;

class ModelEventOrg extends AbstractModelSingle {

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
        if (!$this->getPerson()) {
            trigger_error("Missing person in application ID '" . $this->getPrimary(false) . "'.");
            //throw new InvalidStateException("Missing person in application ID '" . $this->getPrimary(false) . "'.");
        }
        return $this->getPerson()->getFullname();
    }
}
