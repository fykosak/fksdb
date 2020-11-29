<?php

namespace FKSDB\ORM\ModelsMulti\Events;

use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\ModelsMulti\AbstractModelMulti;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelEventParticipant getMainModel()
 */
class ModelMFyziklaniParticipant extends AbstractModelMulti implements IEventReferencedModel {
    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }
}
