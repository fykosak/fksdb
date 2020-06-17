<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Payment\Price;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class AllPersonsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AllPersonsGrid extends BaseGrid {
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
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @param ServicePersonSchedule $servicePersonSchedule
     * @return void
     */
    public function injectServicePersonSchedule(ServicePersonSchedule $servicePersonSchedule) {
        $this->servicePersonSchedule = $servicePersonSchedule;
    }

    protected function getData(): IDataSource {
        $query = $this->servicePersonSchedule->getTable()
            ->where('schedule_item.schedule_group.event_id', $this->event->event_id)
            ->order('person_schedule_id');//->limit(10, 140);
        return new NDataSource($query);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));

        $this->addColumns(['person.person_name']);

        $this->addColumn('schedule_item', _('Schedule item'))->setRenderer(function (ModelPersonSchedule $model) {
            return $model->getScheduleItem()->getLabel();
        })->setSortable(false);
        $this->addColumn('schedule_group', _('Schedule group'))->setRenderer(function (ModelPersonSchedule $model) {
            return $model->getScheduleItem()->getScheduleGroup()->getLabel();
        })->setSortable(false);

        $this->addColumn('price', _('Price'))->setRenderer(function (ModelPersonSchedule $model) {
            return $model->getScheduleItem()->getPrice(Price::CURRENCY_EUR)->__toString() .
                '/' . $model->getScheduleItem()->getPrice(Price::CURRENCY_CZK)->__toString();
        })->setSortable(false);

        $this->addColumns(['event.role', 'payment.payment']);
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
