<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class AllPersonsGrid extends BaseGrid
{

    private PersonScheduleService $personScheduleService;
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectServicePersonSchedule(PersonScheduleService $personScheduleService): void
    {
        $this->personScheduleService = $personScheduleService;
    }

    protected function getData(): TypedSelection
    {
        return $this->personScheduleService->getTable()
            ->where('schedule_item.schedule_group.event_id', $this->event->event_id)
            ->order('person_schedule_id');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumns(
            [
                'person.full_name',
                'schedule_item.name',
                'schedule_group.name',
                'schedule_item.price_czk',
                'schedule_item.price_eur',
                'event.role',
                'payment.payment',
            ]
        );
    }
}
