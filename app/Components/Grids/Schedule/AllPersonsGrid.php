<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class AllPersonsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AllPersonsGrid extends EntityGrid {

    private ModelEvent $event;

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, ServicePersonSchedule::class, [
            'person.full_name',
            'schedule_item.name',
            'schedule_group.name',
            'schedule_item.price_czk',
            'schedule_item.price_eur',
            'event.role',
            'payment.payment',
        ], [
            'schedule_item.schedule_group.event_id', $this->event->event_id,
        ]);
        $this->event = $event;
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        $this->paginate = false;
        $this->addColumn('person_schedule_id', _('#'));
        parent::configure($presenter);
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}
