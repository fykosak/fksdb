<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelEventParticipant extends AbstractModelSingle {

    /**
     * @return ModelPerson|null
     */
    public function getPerson() {
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

?>
