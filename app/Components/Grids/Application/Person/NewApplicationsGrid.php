<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class NewApplicationsGrid extends BaseGrid
{

    protected EventService $eventService;

    protected EventDispatchFactory $eventDispatchFactory;

    final public function injectPrimary(EventService $eventService, EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventService = $eventService;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    protected function getData(): IDataSource
    {
        $events = $this->eventService->getTable()
            ->where('registration_begin <= NOW()')
            ->where('registration_end >= NOW()');
        return new NDataSource($events);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumns([
            'event.name',
            'contest.contest',
        ]);
        $this->addButton('create')
            ->setText(_('Create application'))
            ->setLink(function (EventModel $event): string {
                if ($event->isTeamEvent()) {
                    return $this->getPresenter()
                        ->link(':Event:TeamApplication:create', ['eventId' => $event->event_id]);
                }
                return $this->getPresenter()->link(':Public:Application:default', ['eventId' => $event->event_id]);
            })->setShow(function (EventModel $modelEvent): bool {
                try {
                    return (bool)count(
                        $this->eventDispatchFactory->getEventMachine($modelEvent)->getAvailableTransitions(
                            $this->eventDispatchFactory->getDummyHolder($modelEvent),
                            EventParticipantStatus::tryFrom(Machine::STATE_INIT)
                        )
                    );
                } catch (\Throwable $exception) {
                    return $modelEvent->isRegistrationOpened();
                }
            });
    }
}
