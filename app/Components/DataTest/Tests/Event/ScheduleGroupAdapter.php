<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Event;

use FKSDB\Components\DataTest\Tests\Adapter;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<EventModel,ScheduleGroupModel>
 */
class ScheduleGroupAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        return $model->getScheduleGroups(); // @phpstan-ignore-line
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In schedule group "%s"(%d): '), $model->name_en, $model->schedule_group_id);
    }

    public function getId(): string
    {
        return 'ScheduleGroup' . $this->test->getId();
    }
}