<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;

enum SectionGroupOptions
{
    case GroupBegin;
    case GroupNone;

    public function getGroupLabel(ScheduleGroupModel $group): ?string
    {
        switch ($this) {
            case self::GroupNone:
                return null;
            case self::GroupBegin:
                return $group->start->format(_('__date'));
        }
    }

    public function getGroupKey(ScheduleGroupModel $group): string
    {
        switch ($this) {
            case self::GroupNone:
                return 'none';
            case self::GroupBegin:
                return $group->start->format('Y_m_d');
        }
    }
}
