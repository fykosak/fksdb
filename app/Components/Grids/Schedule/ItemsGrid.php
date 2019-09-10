<?php


namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ItemsGrid
 * @package FKSDB\Components\Grids\Schedule
 */
class ItemsGrid extends BaseGrid {

    /**
     * @param ModelScheduleGroup $group
     */
    public function setGroup(ModelScheduleGroup $group) {
        $items = $group->getItems();
        $dataSource = new NDataSource($items);
        $this->setDataSource($dataSource);
    }

    /**
     * @return string
     */
    public function getTableName(): string {
        return DbNames::TAB_SCHEDULE_ITEM;
    }

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelScheduleItem::class;
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('schedule_item_id', _('#'));
        $this->addColumns(['name_cs', 'name_en', 'price_czk', 'price_eur', 'capacity', 'used_capacity', 'require_id_number']);

        $this->addButton('detail', _('Detail'))->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('item', ['id' => $row->schedule_item_id]);
            });
    }

}
