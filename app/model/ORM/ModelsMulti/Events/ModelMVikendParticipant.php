<?php

namespace FKSDB\ORM\ModelsMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelEventParticipant getMainModel()
 */
class ModelMVikendParticipant extends AbstractModelMulti implements IEventReferencedModel {

    public function __toString(): string {
        return $this->getMainModel()->getPerson()->getFullName();
    }

    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }
}
