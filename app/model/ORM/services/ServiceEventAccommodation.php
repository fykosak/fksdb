<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\DbNames;
use Nette\Database\Table\Selection;

/**
 * Class FKSDB\ORM\Services\ServiceEventAccommodation
 * @deprecated
 */
class ServiceEventAccommodation extends \AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_ACCOMMODATION;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelEventAccommodation';

    /**
     * @param $eventId
     * @return Selection
     */
    public function getAccommodationForEvent($eventId): Selection {
        return $this->getTable()->where('event_id', $eventId);
    }
}
