<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Events\EventRole;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\YearCalculator;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class DispatchGrid
 * @package FKSDB\Components\Grids\Events
 */
class DispatchGrid extends BaseGrid {

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;
    /**
     * @var ModelPerson
     */
    private $person;
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * DispatchGrid constructor.
     * @param ServiceEvent $serviceEvent
     * @param ModelPerson $person
     * @param YearCalculator $yearCalculator
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServiceEvent $serviceEvent, ModelPerson $person, YearCalculator $yearCalculator, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->person = $person;
        $this->serviceEvent = $serviceEvent;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $events = $this->serviceEvent->getTable();
        $dataSource = new NDataSource($events);
        $this->setDataSource($dataSource);
        $this->setDefaultOrder('begin DESC');

        $this->addColumn('event_id', _('Event Id'))->setRenderer(function ($row) {
            return '#' . $row->event_id;
        });
        foreach (['event_type', 'name', 'year'] as $field) {
            $this->addReflectionColumn(DbNames::TAB_EVENT, $field, ModelEvent::class);
        }
        $this->addColumn('roles', _('Roles'))->setRenderer(function ($row) {
            $modelEvent = ModelEvent::createFromActiveRow($row);
            return EventRole::getHtml($this->person, $modelEvent);
        })->setSortable(false);

        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('Dashboard:default', [
                    'eventId' => $row->event_id,
                ]);
            })->setClass('btn btn-sm btn-primary');
    }
}
