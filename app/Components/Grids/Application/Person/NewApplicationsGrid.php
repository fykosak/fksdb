<?php

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Model\Events\EventDispatchFactory;
use FKSDB\Model\Events\Machine\BaseMachine;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Services\ServiceEvent;
use FKSDB\Model\Transitions\Machine\Machine;
use Nette\Application\IPresenter;
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

    final public function injectPrimary(ServiceEvent $serviceEvent, EventDispatchFactory $eventDispatchFactory): void {
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
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
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
