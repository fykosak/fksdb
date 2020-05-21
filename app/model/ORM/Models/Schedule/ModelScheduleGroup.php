<?php

namespace FKSDB\ORM\Models\Schedule;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\IResource;

/**
 * Class ModelScheduleGroup
 * @package FKSDB\ORM\Models\Schedule
 * @property-read int schedule_group_id
 * @property-read string schedule_group_type
 * @property-read int event_id
 * @property-read ActiveRow event
 * @property-read \DateTimeInterface start
 * @property-read \DateTimeInterface end
 * @property-read string name_cs
 * @property-read string name_en
 */
class ModelScheduleGroup extends AbstractModelSingle implements IEventReferencedModel, IResource {

    const RESOURCE_ID = 'event.scheduleGroup';

    const TYPE_ACCOMMODATION = 'accommodation';
    const TYPE_VISA = 'visa';
    const TYPE_ACCOMMODATION_GENDER = 'accommodation_gender';
    const TYPE_ACCOMMODATION_TEACHER = 'accommodation_teacher';
    const TYPE_TEACHER_PRESENT = 'teacher_present';
    const TYPE_WEEKEND = 'weekend';
    const TYPE_WEEKEND_INFO = 'weekend_info';

    public function getItems(): GroupedSelection {
        return $this->related(DbNames::TAB_SCHEDULE_ITEM);
    }

    public function getEvent(): ModelEvent {
        return ModelEvent::createFromActiveRow($this->event);
    }

    /**
     * @return string
     * Label include datetime from schedule group
     */
    public function getLabel(): string {
        return $this->name_cs . '/' . $this->name_en;
    }

    public function __toArray(): array {
        return [
            'scheduleGroupId' => $this->schedule_group_id,
            'scheduleGroupType' => $this->schedule_group_type,
            'label' => [
                'cs' => $this->name_cs,
                'en' => $this->name_en,
            ],
            'eventId' => $this->event_id,
            'start' => $this->start->format('c'),
            'end' => $this->end->format('c'),
        ];
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
