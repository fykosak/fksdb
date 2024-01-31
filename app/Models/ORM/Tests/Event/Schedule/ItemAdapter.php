<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event\Schedule;

use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<ScheduleGroupModel,ScheduleItemModel>
 */
final class ItemAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        return $model->getItems(); // @phpstan-ignore-line
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In schedule item "%s"(%d)'), $model->name_en, $model->schedule_item_id);
    }

    public function getId(): string
    {
        return 'scheduleGroupToItem';
    }
}
