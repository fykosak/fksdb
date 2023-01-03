<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\RelatedGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use Nette\DI\Container;

class ItemsGrid extends RelatedGrid
{

    public function __construct(Container $container, ScheduleGroupModel $group)
    {
        parent::__construct($container, $group, 'schedule_item');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        parent::configure();
        $this->addColumns([
            'schedule_item.schedule_item_id',
            'schedule_item.name_cs',
            'schedule_item.name_en',
            'schedule_item.price_czk',
            'schedule_item.price_eur',
            'schedule_item.capacity',
            'schedule_item.used_capacity',
            'schedule_item.require_id_number',
        ]);
        $this->paginate = false;
        $this->addPresenterButton('ScheduleItem:detail', 'detail', _('Detail'), true, ['id' => 'schedule_item_id']);
        $this->addPresenterButton('ScheduleItem:edit', 'edit', _('Edit'), true, ['id' => 'schedule_item_id']);
    }
}
