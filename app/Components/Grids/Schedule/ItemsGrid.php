<?php


namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ItemsGrid
 * @package FKSDB\Components\Grids\Schedule
 */
class ItemsGrid extends BaseGrid {
    /**
     * @var ModelScheduleGroup
     */
    private $group;

    /**
     * ItemsGrid constructor.
     * @param Container $container
     * @param ModelScheduleGroup $group
     */
    public function __construct(Container $container, ModelScheduleGroup $group) {
        parent::__construct($container);
        $this->group = $group;
    }

    public function getModelClassName(): string {
        return ModelScheduleItem::class;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $items = $this->group->getItems();
        $dataSource = new NDataSource($items);
        $this->setDataSource($dataSource);
        $this->paginate = false;
        $this->addColumn('schedule_item_id', _('#'));
        $this->addColumns([
            DbNames::TAB_SCHEDULE_ITEM . '.name_cs',
            DbNames::TAB_SCHEDULE_ITEM . '.name_en',
            DbNames::TAB_SCHEDULE_ITEM . '.price_czk',
            DbNames::TAB_SCHEDULE_ITEM . '.price_eur',
            DbNames::TAB_SCHEDULE_ITEM . '.capacity',
            DbNames::TAB_SCHEDULE_ITEM . '.used_capacity',
            DbNames::TAB_SCHEDULE_ITEM . '.require_id_number',
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), true, ['id' => 'schedule_item_id']);
    }
}
