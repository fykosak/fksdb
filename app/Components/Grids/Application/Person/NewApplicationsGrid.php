<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends BaseGrid<EventModel,array{}>
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
        $this->addSimpleReferencedColumns([
            '@event.name',
            '@contest.contest',
        ]);
        $button = new Button(
            $this->container,
            $this->getPresenter(),
            new Title(null, _('Create application')),
            fn(EventModel $event): array => $event->isTeamEvent()
                ? [':Events:Team:create', ['eventId' => $event->event_id]]
                : [':Public:Application:default', ['eventId' => $event->event_id]],
            null,
            fn(EventModel $modelEvent): bool => $modelEvent->isRegistrationOpened()
        );
        $this->addTableButton($button, 'create');
    }
}
