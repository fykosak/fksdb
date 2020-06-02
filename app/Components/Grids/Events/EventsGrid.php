<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class EventsGrid extends BaseGrid {

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;
    /**
     * @var ModelContest
     */
    private $contest;
    /**
     * @var int
     */
    private $year;

    /**
     * EventsGrid constructor.
     * @param Container $container
     * @param ModelContest $contest
     * @param int $year
     */
    public function __construct(Container $container, ModelContest $contest, int $year) {
        parent::__construct($container);
        $this->contest = $contest;
        $this->year = $year;
    }

    /**
     * @param ServiceEvent $serviceEvent
     * @return void
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function getData(): IDataSource {
        $events = $this->serviceEvent->getEvents($this->contest, $this->year);
        return new NDataSource($events);
    }

    /**
     * @param Presenter $presenter
     * @throws BadRequestException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);
        $this->setDefaultOrder('event.begin ASC');

        $this->addColumns([
            'event.event_id',
            'event.event_type',
            'event.name',
            'event.year',
            'event.event_year',
        ]);

        $this->addLinkButton(':Event:dashboard:default', 'detail', _('Detail'), true, ['eventId' => 'event_id']);
        $this->addLinkButton('edit', 'edit', _('Edit'), true, ['id' => 'event_id']);

        $this->addLink('event_participant.list');

        $this->addLinkButton(':Event:EventOrg:list', 'org', _('Organisers'), true, ['eventId' => 'event_id']);

        $this->addGlobalButton('add')
            ->setLink($this->getPresenter()->link('create'))
            ->setLabel('Add event')
            ->setClass('btn btn-sm btn-primary');
    }

    protected function getModelClassName(): string {
        return ModelEvent::class;
    }

}
