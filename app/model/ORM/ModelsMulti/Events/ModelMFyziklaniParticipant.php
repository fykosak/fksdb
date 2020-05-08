<?php

namespace FKSDB\ORM\ModelsMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMFyziklaniParticipant extends AbstractModelMulti implements IEventReferencedModel {
    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }
}
