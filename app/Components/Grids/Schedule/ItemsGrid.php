<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\RelatedGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class ItemsGrid extends RelatedGrid
{

    public function __construct(Container $container, ModelScheduleGroup $group)
    {
        parent::__construct($container, $group, 'schedule_item');
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
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
        $this->addLinkButton('ScheduleItem:detail', 'detail', _('Detail'), true, ['id' => 'schedule_item_id']);
        $this->addLinkButton('ScheduleItem:edit', 'edit', _('Edit'), true, ['id' => 'schedule_item_id']);
    }

    protected function getModelClassName(): string
    {
        return ModelScheduleItem::class;
    }
}
