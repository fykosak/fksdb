<?php

namespace FKSDB\Models\ORM\ModelsMulti\Events;

use FKSDB\Models\ORM\Models\IEventReferencedModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelEventParticipant getMainModel()
 */
class ModelMDsefParticipant extends AbstractModelMulti implements IEventReferencedModel {

    public function __toString(): string {
        if (!$this->getMainModel()->getPerson()) {
            trigger_error("Missing person in '" . $this->getMainModel() . "'.");
            //throw new InvalidStateException("Missing person in '" . $this->getMainModel() . "'.");
        }
        return $this->getMainModel()->getPerson()->getFullName();
    }

    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }
}
