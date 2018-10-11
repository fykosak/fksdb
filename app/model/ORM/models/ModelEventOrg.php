<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 * Class FKSDB\ORM\ModelEventOrg
 * @property ActiveRow person
 * @property ActiveRow event
 */
class ModelEventOrg extends AbstractModelSingle implements IResource {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        return ModelPerson::createFromTableRow($this->person);
    }

    /**
     * @return ModelEvent
     */
    public function getEvent() {
        return ModelEvent::createFromTableRow($this->event);
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
