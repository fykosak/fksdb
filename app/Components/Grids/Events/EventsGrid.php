<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\BaseGrid;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Database\Table\Selection;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use OrgModule\OrgPresenter;
use ServiceEvent;
use SQL\SearchableDataSource;

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

    function __construct(ServiceEvent $serviceEvent) {
        parent::__construct();
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

        $dataSource = new SearchableDataSource($events);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('event.name LIKE CONCAT(\'%\', ? , \'%\') OR event_type.name LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
            }
        });
        $this->setDefaultOrder('event.begin ASC');
        $this->setDataSource($dataSource);

        //
        // columns
        //
        $this->addColumn('event_id', _('Id akce'));
        $this->addColumn('type_name', _('Typ akce'));
        $this->addColumn('name', _('Name'));
        $this->addColumn('year', _('Ročník semináře'));
        $this->addColumn('event_year', _('Ročník akce'));
        //
        // operations
        //

        $this->addButton('model', _('Model'))
            ->setText(_('Model'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:Model:', ['eventId' => $row->event_id]);
            });
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->event_id);
            });
        $this->addButton('applications')
            ->setText(_('Applications'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('applications', $row->event_id);
            });
        $this->addButton('org')
            ->setText(_('Organisers'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('EventOrg:list', ['eventId' => $row->event_id]);
            });
        $this->addButton('accommodation')
            ->setText(_('Accommodation'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:Accommodation:list', ['eventId' => $row->event_id]);
            });

        $this->addButton('schedule')
            ->setText(_('Schedule'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:Schedule:', ['eventId' => $row->event_id]);
            });

        $this->addGlobalButton('add')
            ->setLink($this->getPresenter()->link('create'))
            ->setLabel('Add event')
            ->setClass('btn btn-sm btn-primary');
    }

}
