<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ItemsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ItemsGrid extends EntityGrid {

    public function __construct(Container $container, ModelScheduleGroup $group) {
        parent::__construct($container, ServiceScheduleItem::class, [
            'schedule_item.schedule_item_id',
            'schedule_item.name_cs',
            'schedule_item.name_en',
            'schedule_item.price_czk',
            'schedule_item.price_eur',
            'schedule_item.capacity',
            'schedule_item.used_capacity',
            'schedule_item.require_id_number',
        ], [
            'schedule_group_id' => $group->schedule_group_id,
        ]);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addLinkButton('ScheduleItem:detail', 'detail', _('Detail'), true, ['id' => 'schedule_item_id']);
        $this->addLinkButton('ScheduleItem:edit', 'edit', _('Edit'), true, ['id' => 'schedule_item_id']);
    }
}
