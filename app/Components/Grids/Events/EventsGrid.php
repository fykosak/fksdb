<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use NiftyGrid\DataSource\NDataSource;
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
     * @var \FKSDB\ORM\Services\ServiceEvent
     */
    private $serviceEvent;

    /**
     * EventsGrid constructor.
     * @param \FKSDB\ORM\Services\ServiceEvent $serviceEvent
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServiceEvent $serviceEvent, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param OrgPresenter $presenter
     * @throws BadRequestException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws \NiftyGrid\DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        $events = $this->serviceEvent->getEvents($presenter->getSelectedContest(), $presenter->getSelectedYear());

        $dataSource = new NDataSource($events);

        $this->setDefaultOrder('event.begin ASC');
        $this->setDataSource($dataSource);

        $this->addColumn('event_id', _('Id akce'));

        foreach (['event_type', 'name', 'event_year'] as $field) {
            $this->addReflectionColumn(DbNames::TAB_EVENT, $field, ModelEvent::class);
        }
        //
        // operations
        //
        $this->addButton('detail')
            ->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:dashboard:', ['eventId' => $row->event_id]);
            });
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->event_id);
            });
        $this->addButton('applications')
            ->setText(_('Applications'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:application:list', ['eventId' => $row->event_id]);
            })->setShow(function ($row) {
                return $this->getPresenter()->authorized(':Event:application:list', ['eventId' => $row->event_id]);
            });

        $this->addButton('teamApplications')
            ->setText(_('Team applications'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:teamApplication:list', ['eventId' => $row->event_id]);
            })->setShow(function ($row) {
                return $this->getPresenter()->authorized(':Event:teamApplication:list', ['eventId' => $row->event_id]);
            });

        $this->addButton('org')
            ->setText(_('Organisers'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('EventOrg:list', ['eventId' => $row->event_id]);
            });

        $this->addGlobalButton('add')
            ->setLink($this->getPresenter()->link('create'))
            ->setLabel('Add event')
            ->setClass('btn btn-sm btn-primary');
    }
}
