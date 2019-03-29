<?php


namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Payment\Price;
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
     * @param $presenter
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('schedule_item_id', _('#'));
        $this->addColumn('name_cs', _('CS Name'));
        $this->addColumn('name_en', _('EN Name'));

        $this->addColumn('price_czk', _('Price CZK'))->setRenderer(function ($row) {
            $model = ModelScheduleItem::createFromTableRow($row);
            return $model->getPrice(Price::CURRENCY_CZK);
        });
        $this->addColumn('price_eur', _('Price EUR'))->setRenderer(function ($row) {
            $model = ModelScheduleItem::createFromTableRow($row);
            return $model->getPrice(Price::CURRENCY_EUR);
        });
        $this->addColumn('capacity', _('Capacity'))->setRenderer(function ($row) {
            $model = ModelScheduleItem::createFromTableRow($row);
            return $model->getUsedCapacity() . '/' . $model->getCapacity();
        });
        $this->addColumn('require_id_number', _('Require "Id Number"'))->setRenderer(function ($row) {
            return $row->require_id_number ? _('true') : _('false');
        });
        $this->addButton('detail', _('Detail'))->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('item', ['id' => $row->schedule_item_id]);
            });
    }

}
