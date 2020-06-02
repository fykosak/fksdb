<?php

namespace FKSDB\ORM\ModelsMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\InvalidStateException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelEventParticipant getMainModel()
 */
class ModelMDsefParticipant extends AbstractModelMulti implements IEventReferencedModel {

    public function __toString(): string {
        if (!$this->getMainModel()->getPerson()) {
            throw new InvalidStateException("Missing person in '" . $this->getMainModel() . "'.");
        }
        return $this->getMainModel()->getPerson()->getFullName();
    }

    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }
}
