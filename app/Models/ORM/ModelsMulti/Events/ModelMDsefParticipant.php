<?php

namespace FKSDB\Models\ORM\ModelsMulti\Events;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read  ModelEventParticipant $mainModel
 */
class ModelMDsefParticipant extends AbstractModelMulti {

    public function __toString(): string {
        return $this->mainModel->getPerson()->getFullName();
    }

    public function getEvent(): ModelEvent {
        return $this->mainModel->getEvent();
    }

    public function getPerson(): ModelPerson {
        return $this->mainModel->getPerson();
    }
}
