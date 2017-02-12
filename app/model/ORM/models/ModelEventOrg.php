<?php

use Nette\InvalidStateException;

class ModelEventOrg extends AbstractModelSingle {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        $this->person_id; // stupid touch
        $row = $this->ref(DbNames::TAB_PERSON, 'person_id');
        return $row ? ModelPerson::createFromTableRow($row) : null;
    }

    public function __toString() {
        if (!$this->getPerson()) {
            throw new InvalidStateException("Missing person in application ID '" . $this->getPrimary(false) . "'.");
        }
        return $this->getPerson()->getFullname();
    }
}
