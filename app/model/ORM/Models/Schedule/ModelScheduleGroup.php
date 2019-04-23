<?php


namespace FKSDB\ORM\Models\Schedule;

use DateTime;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;

/**
 * Class ModelScheduleGroup
 * @package FKSDB\ORM\Models\Schedule
 * @property-readint schedule_group_id
 * @property-readstring schedule_group_type
 * @property-readint event_id
 * @property-readActiveRow event
 * @property-readDateTime start
 * @property-readDateTime end
 */
class ModelScheduleGroup extends \FKSDB\ORM\AbstractModelSingle {
    const TYPE_ACCOMMODATION = 'accommodation';
    /**
     * @return GroupedSelection
     */
    public function getItems(): GroupedSelection {
        return $this->related(DbNames::TAB_SCHEDULE_ITEM);
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
    }

}
