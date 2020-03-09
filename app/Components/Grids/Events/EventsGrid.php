<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use OrgModule\OrgPresenter;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class EventsGrid extends BaseGrid {

    /**
     *
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * EventsGrid constructor.
     * @param ServiceEvent $serviceEvent
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServiceEvent $serviceEvent, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param ModelContest $contest
     * @param int $year
     */
    public function setParams(ModelContest $contest,int $year){
        $events = $this->serviceEvent->getEvents($contest, $year);
        $dataSource = new NDataSource($events);
        $this->setDefaultOrder('event.begin ASC');
        $this->setDataSource($dataSource);
    }

    /**
     * @param OrgPresenter $presenter
     * @throws BadRequestException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $events = $this->serviceEvent->getEvents($presenter->getSelectedContest(), $presenter->getSelectedYear());

        $dataSource = new NDataSource($events);

        $this->setDefaultOrder('event.begin ASC');
        $this->setDataSource($dataSource);

        $this->addColumn('event_id', _('Id akce'));

        $this->addColumns([
            DbNames::TAB_EVENT . '.event_type',
            DbNames::TAB_EVENT . '.name',
            DbNames::TAB_EVENT . '.year',
            DbNames::TAB_EVENT . '.event_year',
        ]);

        $this->addLinkButton($this->getPresenter(), ':Event:dashboard:default', 'detail', _('Detail'), true, ['eventId' => 'event_id']);
        $this->addLinkButton($this->getPresenter(), 'edit', 'edit', _('Edit'), true, ['id' => 'event_id']);

        $this->addLink('event_participant.list');

        $this->addLinkButton($this->getPresenter(), 'EventOrg:list', 'org', _('Organisers'), true, ['eventId' => 'event_id']);

        $this->addGlobalButton('add')
            ->setLink($this->getPresenter()->link('create'))
            ->setLabel('Add event')
            ->setClass('btn btn-sm btn-primary');
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelEvent::class;
    }

}
