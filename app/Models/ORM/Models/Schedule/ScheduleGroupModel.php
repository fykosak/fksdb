<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int schedule_group_id
 * @property-read ScheduleGroupType schedule_group_type
 * @property-read int event_id
 * @property-read EventModel event
 * @property-read \DateTimeInterface start
 * @property-read \DateTimeInterface end
 * @property-read string name_cs
 * @property-read string name_en
 */
class ScheduleGroupModel extends Model implements Resource
{

    public const RESOURCE_ID = 'event.scheduleGroup';

    public function getItems(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_SCHEDULE_ITEM);
    }

    /**
     * Label include datetime from schedule group
     */
    public function getLabel(): string
    {
        return $this->name_cs . '/' . $this->name_en;
    }

    public function __toArray(): array
    {
        return [
            'scheduleGroupId' => $this->schedule_group_id,
            'scheduleGroupType' => $this->schedule_group_type->value,
            'label' => [
                'cs' => $this->name_cs,
                'en' => $this->name_en,
            ],
            'eventId' => $this->event_id,
            'start' => $this->start->format('c'),
            'end' => $this->end->format('c'),
        ];
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @param string $key
     * @return ScheduleGroupType|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'schedule_group_type':
                $value = ScheduleGroupType::tryFrom($value);
                break;
        }
        return $value;
    }
}
