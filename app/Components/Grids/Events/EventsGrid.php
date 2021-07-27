<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class EventsGrid extends EntityGrid {

    public function __construct(Container $container, ModelContestYear $contestYear) {
        parent::__construct($container, ServiceEvent::class, [
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

    /**
     * @param Presenter $presenter
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->setDefaultOrder('event.begin ASC');

        $this->addLinkButton(':Event:Dashboard:default', 'detail', _('Detail'), true, ['eventId' => 'event_id']);
        $this->addLinkButton('edit', 'edit', _('Edit'), true, ['id' => 'event_id']);

        $this->addLink('event.application.list');

        $this->addLinkButton(':Event:EventOrg:list', 'org', _('Organisers'), true, ['eventId' => 'event_id']);
    }
}
