<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

class EventsGrid extends EntityGrid
{

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        parent::__construct($container, EventService::class, [
            'event.event_id',
            'event.event_type',
            'event.name',
            'event.year',
            'event.event_year',
        ], [
            'event_type.contest_id' => $contestYear->contest_id,
            'year' => $contestYear->year,
        ]);
    }

    protected function getModels(): Selection
    {
        $value = parent::getModels();
        $value->order('event.begin ASC');
        return $value;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        parent::configure();
        $this->addPresenterButton(':Event:Dashboard:default', 'detail', _('Detail'), true, ['eventId' => 'event_id']);
        $this->addPresenterButton('edit', 'edit', _('Edit'), true, ['id' => 'event_id']);

       // $this->addORMLink('event.application.list');

        $this->addPresenterButton(':Event:EventOrg:list', 'org', _('Organizers'), true, ['eventId' => 'event_id']);
    }
}
