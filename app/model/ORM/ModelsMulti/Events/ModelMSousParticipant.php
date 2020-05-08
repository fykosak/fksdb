<?php

namespace FKSDB\ORM\ModelsMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMSousParticipant extends AbstractModelMulti implements IEventReferencedModel {

    const STATE_AUTO_INVITED = 'auto.invited';
    const STATE_AUTO_SPARE = 'auto.spare';

    /**
     * @return mixed
     */
    public function __toString() {
        return $this->getMainModel()->getPerson()->getFullname();
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }

}
