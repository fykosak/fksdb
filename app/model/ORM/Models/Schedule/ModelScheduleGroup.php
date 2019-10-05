<?php

namespace FKSDB\ORM\Models\Schedule;

use DateTime;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\NotImplementedException;
use Tracy\Debugger;

/**
 * Class ModelScheduleGroup
 * @package FKSDB\ORM\Models\Schedule
 * @property-read int schedule_group_id
 * @property-read string schedule_group_type
 * @property-read int event_id
 * @property-read ActiveRow event
 * @property-read DateTime start
 * @property-read DateTime end
 */
class ModelScheduleGroup extends AbstractModelSingle implements IEventReferencedModel {
    const TYPE_ACCOMMODATION = 'accommodation';
    const TYPE_DSEF_GROUP = 'dsef_group';
    const TYPE_VISA_REQUIREMENT = 'visa_requirement';
    const TYPE_ACCOMMODATION_SAME_GENDER_REQUIRED = 'accommodation_same_gender_required';
    const TYPE_ACCOMMODATION_TEACHER_SEPARATED = 'accommodation_teacher_separated';

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
        return ModelEvent::createFromActiveRow($this->event);
    }

    /**
     * @return string
     * Label include datetime from schedule group
     */
    public function getLabel(): string {
        switch ($this->schedule_group_type) {
            case self::TYPE_ACCOMMODATION:
                return \sprintf(_('Accommodation from %s to %s'),
                    $this->start->format('d. m. Y'),
                    $this->end->format('d. m. Y')
                );
            case self::TYPE_ACCOMMODATION_SAME_GENDER_REQUIRED:
                return _('Accommodation with another gender');
            case self::TYPE_VISA_REQUIREMENT:
                return _('Visa to Czech Republic');
            case self::TYPE_ACCOMMODATION_TEACHER_SEPARATED:
                return _('Teacher require specific accommodation');
        }
        throw new NotImplementedException();
    }

    /**
     * @return array
     */
    public function __toArray(): array {
        return [
            'scheduleGroupId' => $this->schedule_group_id,
            'scheduleGroupType' => $this->schedule_group_type,
            'label' => $this->getLabel(),
            'eventId' => $this->event_id,
            'start' => $this->start->format('c'),
            'end' => $this->end->format('c'),
        ];
    }
}
