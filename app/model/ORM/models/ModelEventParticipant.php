<?php

use Nette\Database\Table\ActiveRow;
/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property ActiveRow person
 */
class ModelEventParticipant extends AbstractModelSingle {

    /**
     * @return ModelPerson|null
     */
    public function getPerson() {
        return ModelPerson::createFromTableRow($this->person);
    }

    public function __toString() {
        if (!$this->getPerson()) {
            trigger_error("Missing person in application ID '" . $this->getPrimary(false) . "'.");
            //throw new InvalidStateException("Missing person in application ID '" . $this->getPrimary(false) . "'.");
        }
        return $this->getPerson()->getFullname();
    }

}
