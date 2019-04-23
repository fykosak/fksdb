<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\Transitions\IEventReferencedModel;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 * Class FKSDB\ORM\Models\ModelEventOrg
 * @property-read ActiveRow person
 * @property-read ActiveRow event
 * @property-read string note
 */
class ModelEventOrg extends AbstractModelSingle implements IResource, IEventReferencedModel {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'eventOrg';
    }

    /**
     * @return string
     */
    public function __toString(): string {
        if (!$this->getPerson()) {
            throw new InvalidStateException("Missing person in application ID '" . $this->getPrimary(false) . "'.");
        }
        return $this->getPerson()->__toString();
    }
}
