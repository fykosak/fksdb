<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ModelsMulti\Events;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ModelsMulti\ModelMulti;

/**
 * @property-read EventParticipantModel $mainModel
 * @deprecated
 */
class ModelMFyziklaniParticipant extends ModelMulti
{

    public function getEvent(): EventModel
    {
        return $this->mainModel->event;
    }

    public function getPerson(): PersonModel
    {
        return $this->mainModel->person;
    }
}
