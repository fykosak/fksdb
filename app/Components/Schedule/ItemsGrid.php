<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<ScheduleItemModel>
 */
class ItemsGrid extends BaseGrid
{
    private ScheduleGroupModel $group;

    public function __construct(Container $container, ScheduleGroupModel $group)
    {
        parent::__construct($container);
        $this->group = $group;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ScheduleItemModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->group->getItems();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumns([
            'schedule_item.schedule_item_id',
            'schedule_item.name_cs',
            'schedule_item.name_en',
            'schedule_item.price_czk',
            'schedule_item.price_eur',
            'schedule_item.capacity',
            'schedule_item.used_capacity',
        ]);
        $this->paginate = false;
        $this->addPresenterButton(':Schedule:Item:detail', 'detail', _('Detail'), true, ['id' => 'schedule_item_id']);
        $this->addPresenterButton(':Schedule:Item:edit', 'edit', _('Edit'), true, ['id' => 'schedule_item_id']);
    }
}
