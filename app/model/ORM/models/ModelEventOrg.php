<?php

use FKSDB\ORM\ModelPerson;
use Nette\InvalidStateException;
use Nette\Security\IResource;

class ModelEventOrg extends AbstractModelSingle implements IResource {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        $this->person_id; // stupid touch
        $row = $this->ref(DbNames::TAB_PERSON, 'person_id');
        return $row ? ModelPerson::createFromTableRow($row) : null;
    }

    /**
     * @return ModelEvent
     */
    public function getEvent() {
        return ModelEvent::createFromTableRow($this->ref(DbNames::TAB_EVENT, 'event_id'));
    }

    public function getResourceId() {
        return 'eventOrg';
    }

    public function __toString() {
        if (!$this->getPerson()) {
            throw new InvalidStateException("Missing person in application ID '" . $this->getPrimary(false) . "'.");
        }
        return $this->getPerson()->getFullname();
    }
}
