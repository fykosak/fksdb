<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEventAccommodation;
use Nette\Database\Table\Selection;

/**
 * Class FKSDB\ORM\Services\ServiceEventAccommodation
 * @deprecated
 */
class ServiceEventAccommodation extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelEventAccommodation::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_EVENT_ACCOMMODATION;
    }

    /**
     * @param $eventId
     * @return Selection
     */
    public function getAccommodationForEvent($eventId): Selection {
        return $this->getTable()->where('event_id', $eventId);
    }
}
