<?php

namespace FKSDB\Model\ORM\ModelsMulti\Events;

use FKSDB\Model\ORM\Models\IEventReferencedModel;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Models\ModelEventParticipant;
use FKSDB\Model\ORM\ModelsMulti\AbstractModelMulti;

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
