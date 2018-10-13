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

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
    }

    public function getResourceId(): string {
        return 'eventOrg';
    }

    public function __toString() {
        if (!$this->getPerson()) {
            throw new InvalidStateException("Missing person in application ID '" . $this->getPrimary(false) . "'.");
        }
        return $this->getPerson()->getFullname();
    }
}
