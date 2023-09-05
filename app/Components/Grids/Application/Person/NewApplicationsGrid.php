<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends BaseGrid<EventModel>
 */
class NewApplicationsGrid extends BaseGrid
{
    protected EventService $eventService;

    protected EventDispatchFactory $eventDispatchFactory;

    final public function injectPrimary(EventService $eventService, EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventService = $eventService;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @phpstan-return TypedSelection<EventModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->eventService->getEventsWithOpenRegistration();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        $this->addColumns([
            'event.name',
            'contest.contest',
        ]);
        $button = new PresenterButton(
            $this->container,
            null,
            new Title(null, _('Create application')),
            fn(EventModel $event): array => $event->isTeamEvent()
                ? [':Event:TeamApplication:create', ['eventId' => $event->event_id]]
                : [':Public:Application:default', ['eventId' => $event->event_id]],
            null,
            function (EventModel $modelEvent): bool {
                try {
                    return (bool)count(
                        $this->eventDispatchFactory->getParticipantMachine($modelEvent)->getAvailableTransitions(
                            $this->eventDispatchFactory->getDummyHolder($modelEvent),
                            EventParticipantStatus::from(Machine::STATE_INIT)
                        )
                    );
                } catch (\Throwable $exception) {
                    return true;
                }
            }
        );
        $this->addButton($button, 'create');
    }
}
