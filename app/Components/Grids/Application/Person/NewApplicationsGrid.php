<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\ListComponent\Button\PresenterButton;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;

class NewApplicationsGrid extends BaseGrid
{
    protected EventService $eventService;

    protected EventDispatchFactory $eventDispatchFactory;

    final public function injectPrimary(EventService $eventService, EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventService = $eventService;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    protected function setData(): void
    {
        $this->data = $this->eventService->getTable()
            ->where('registration_begin <= NOW()')
            ->where('registration_end >= NOW()');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumns([
            'event.name',
            'contest.contest',
        ]);
        $button = new PresenterButton(
            $this->container,
            new Title(null, _('Create application')),
            fn(EventModel $event): array => $event->isTeamEvent()
                ? [':Event:TeamApplication:create', ['eventId' => $event->event_id]]
                : [':Public:Application:default', ['eventId' => $event->event_id]],
            null,
            function (EventModel $modelEvent): bool {
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
            }
        );
        $this->getColumnsContainer()->getButtonContainer()->addComponent($button, 'create');
    }
}
