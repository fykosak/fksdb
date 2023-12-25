<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<EventModel,ScheduleItemModel>
 */
class ScheduleItemAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        $items = [];
        /** @var ScheduleGroupModel $group */
        foreach ($model->getScheduleGroups() as $group) {
            /** @var ScheduleItemModel $item */
            foreach ($group->getItems() as $item) {
                $items[] = $item;
            }
        }
        return $items;
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In schedule item "%s"(%d): '), $model->name_en, $model->schedule_item_id);
    }

    public function getId(): string
    {
        return 'ScheduleItem' . $this->test->getId();
    }
}
