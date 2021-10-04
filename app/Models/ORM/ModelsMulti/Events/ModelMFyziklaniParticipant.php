<?php

namespace FKSDB\Models\ORM\ModelsMulti\Events;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;

/**
 * @property-read ModelEventParticipant $mainModel
 */
class ModelMFyziklaniParticipant extends AbstractModelMulti
{

    public function getEvent(): ModelEvent
    {
        return $this->mainModel->getEvent();
    }

    public function getPerson(): ModelPerson
    {
        return $this->mainModel->getPerson();
    }
}
