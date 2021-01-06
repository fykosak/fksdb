<?php

namespace FKSDB\Models\ORM\ModelsMulti\Events;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelEventParticipant getMainModel()
 */
class ModelMFyziklaniParticipant extends AbstractModelMulti {

    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }
}
