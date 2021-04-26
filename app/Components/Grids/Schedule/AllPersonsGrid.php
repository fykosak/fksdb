<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
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

    private ServicePersonSchedule $servicePersonSchedule;

    private ModelEvent $event;

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectServicePersonSchedule(ServicePersonSchedule $servicePersonSchedule): void {
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
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('person_schedule_id', _('#'));
        $this->addColumns(['person.full_name', 'schedule_item.name', 'schedule_group.name', 'schedule_item.price_czk', 'schedule_item.price_eur', 'event.role', 'payment.payment']);
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
