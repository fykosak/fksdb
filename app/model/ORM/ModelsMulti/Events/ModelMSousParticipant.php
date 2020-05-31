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
class ModelMSousParticipant extends AbstractModelMulti implements IEventReferencedModel {

    public const STATE_AUTO_INVITED = 'auto.invited';
    public const STATE_AUTO_SPARE = 'auto.spare';

    /**
     * @return string
     */
    public function __toString() {
        return $this->getMainModel()->getPerson()->getFullName();
    }

    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }
}
