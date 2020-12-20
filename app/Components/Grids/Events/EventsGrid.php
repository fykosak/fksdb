<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class EventsGrid extends EntityGrid {

    public function __construct(Container $container, ModelContest $contest, int $year) {
        parent::__construct($container, ServiceEvent::class, [
            'event.event_id',
            'event.event_type',
            'event.name',
            'event.year',
            'event.event_year',
        ], [
            'event_type.contest_id' => $contest->contest_id,
            'year' => $year,
        ]);
    }

    /**
     * @param IPresenter $presenter
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->setDefaultOrder('event.begin ASC');

        $this->addLinkButton(':Event:Dashboard:default', 'detail', _('Detail'), true, ['eventId' => 'event_id']);
        $this->addLinkButton('edit', 'edit', _('Edit'), true, ['id' => 'event_id']);

        $this->addLink('event.application.list');

        $this->addLinkButton(':Event:EventOrg:list', 'org', _('Organisers'), true, ['eventId' => 'event_id']);
    }
}
