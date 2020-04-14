<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EventRole;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Payment\Price;
use FKSDB\YearCalculator;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonsGrid
 * @package FKSDB\Components\Grids\Schedule
 */
class AllPersonsGrid extends BaseGrid {
    /**
     * @var YearCalculator
     */
    private $yearCalculator;
    /**
     * @var ServicePersonSchedule
     */
    private $servicePersonSchedule;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * PersonsGrid constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        $this->yearCalculator = $container->getByType(YearCalculator::class);
        $this->servicePersonSchedule = $container->getByType(ServicePersonSchedule::class);
        $this->event = $event;
        parent::__construct($container);
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $query = $this->servicePersonSchedule->getTable()
            ->where('schedule_item.schedule_group.event_id', $this->event->event_id)
            // ->where('person_schedule_id', 508)
            ->order('person_schedule_id');//->limit(10, 140);
        $dataSource = new NDataSource($query);
        $this->setDataSource($dataSource);


        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));

        $this->addColumns(['referenced.person_name']);

        $this->addColumn('schedule_item', _('Schedule item'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem()->getLabel();
        })->setSortable(false);
        $this->addColumn('schedule_group', _('Schedule group'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem()->getScheduleGroup()->getLabel();
        })->setSortable(false);

        $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem()->getPrice(Price::CURRENCY_EUR)->__toString() .
                '/' . $model->getScheduleItem()->getPrice(Price::CURRENCY_CZK)->__toString();
        })->setSortable(false);

        $this->addColumnRole();

        $this->addColumnPayment();
    }

    /**
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     * @throws NotImplementedException
     */
    protected function addColumnPayment() {
        $this->addColumns(['referenced.payment_id']);
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnRole() {
        $this->addColumn('role', _('Role'))
            ->setRenderer(function ($row) {
                $model = ModelPersonSchedule::createFromActiveRow($row);
                return EventRole::calculateRoles($model->getPerson(), $this->event, $this->yearCalculator);
            })->setSortable(false);
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
