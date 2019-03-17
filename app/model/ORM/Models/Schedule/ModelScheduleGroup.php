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
 * @property int schedule_group_id
 * @property string schedule_group_type
 * @property int event_id
 * @property ActiveRow event
 * @property DateTime start
 * @property DateTime end
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
