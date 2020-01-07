<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\GridException;

/**
 * Class PersonsGrid
 * @package FKSDB\Components\Grids\Schedule
 */
class PersonGrid extends BaseGrid {

    /**
     * @param ModelEvent $event
     * @param ModelPerson $person
     */
    public function setData(ModelEvent $event, ModelPerson $person) {
        $query = $person->getScheduleForEvent($event);
        $dataSource = new NDataSource($query);
        $this->setDataSource($dataSource);
    }

    /**
     * @param ModelPerson|null $person
     * @param ModelEvent|null $event
     * @throws BadRequestException
     * @throws GridException
     */
    public function render(ModelPerson $person = null, ModelEvent $event = null) {
        if (!$event || !$person) {
            throw new BadRequestException();
        }
        $this->setData($event, $person);
        parent::render();
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));
        $this->addColumn('group_label', _('Group'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem()->getGroup()->getLabel();
        });
        $this->addColumn('item_label', _('Item'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem()->getLabel();
        });
        $this->addJoinedColumn(DbNames::TAB_SCHEDULE_ITEM, 'price_czk', function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem();
        });
        $this->addJoinedColumn(DbNames::TAB_SCHEDULE_ITEM, 'price_eur', function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem();
        });

        $this->addColumnPayment();

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->state;
        });
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnPayment() {
        $this->addColumn('payment', _('Payment'))
            ->setRenderer(function ($row) {
                $model = ModelPersonSchedule::createFromActiveRow($row);
                $modelPayment = $model->getPayment();
                if (!$modelPayment) {
                    return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText('No payment found');
                }
                // TODO
                return Html::el('span')->addAttributes(['class' => ''])->addText('#' . $modelPayment->getPaymentId() . '-');
            })->setSortable(false);
    }

}
