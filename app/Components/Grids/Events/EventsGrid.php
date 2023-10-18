<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Fykosak\NetteORM\TypedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<EventModel,array{}>
 */
class EventsGrid extends BaseGrid
{
    private EventService $service;
    private ContestYearModel $contestYear;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        parent::__construct($container);
        $this->contestYear = $contestYear;
    }

    public function inject(EventService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<EventModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->service->getTable()->where([
            'event_type.contest_id' => $this->contestYear->contest_id,
            'year' => $this->contestYear->year,
        ])->order('event.begin ASC');
    }


    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addSimpleReferencedColumns([
            '@event.event_id',
            '@event.event_type',
            '@event.name',
            '@event.year',
            '@event.event_year',
        ]);
        $this->addPresenterButton(
            ':Event:Dashboard:default',
            'detail',
            _('button.detail'),
            true,
            ['eventId' => 'event_id']
        );
        $this->addPresenterButton('edit', 'edit', _('button.edit'), true, ['id' => 'event_id']);

        // $this->addORMLink('event.application.list');

        $this->addPresenterButton(
            ':Event:EventOrganizer:list',
            'org',
            _('Organizers'),
            true,
            ['eventId' => 'event_id']
        );
    }
}
