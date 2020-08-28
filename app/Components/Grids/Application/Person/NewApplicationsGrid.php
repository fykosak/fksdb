<?php

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\Transitions\Machine;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class NewApplicationsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NewApplicationsGrid extends BaseGrid {

    protected ServiceEvent $serviceEvent;

    protected EventDispatchFactory $eventDispatchFactory;

    public function injectPrimary(ServiceEvent $serviceEvent, EventDispatchFactory $eventDispatchFactory): void {
        $this->serviceEvent = $serviceEvent;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    protected function getData(): IDataSource {
        $events = $this->serviceEvent->getTable()
            ->where('registration_begin <= NOW()')
            ->where('registration_end >= NOW()');
        return new NDataSource($events);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumns([
            'event.name',
            'contest.contest',
        ]);
        $this->addButton('create')
            ->setText(_('Create application'))
            ->setLink(function (ModelEvent $row): string {
                return $this->getPresenter()->link(':Public:Application:default', ['eventId' => $row->event_id]);
            })
            ->setShow(function (ModelEvent $modelEvent): bool {
                $holder = $this->eventDispatchFactory->getDummyHolder($modelEvent);
                $machine = $this->eventDispatchFactory->getEventMachine($modelEvent);
                $transitions = $machine->getPrimaryMachine()->getAvailableTransitions($holder, Machine::STATE_INIT, BaseMachine::EXECUTABLE | BaseMachine::VISIBLE);
                return (bool)count($transitions);
            });
    }
}
