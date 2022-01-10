<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class NewApplicationsGrid extends BaseGrid
{

    protected ServiceEvent $serviceEvent;

    protected EventDispatchFactory $eventDispatchFactory;

    final public function injectPrimary(ServiceEvent $serviceEvent, EventDispatchFactory $eventDispatchFactory): void
    {
        $this->serviceEvent = $serviceEvent;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    protected function getData(): IDataSource
    {
        $events = $this->serviceEvent->getTable()
            ->where('registration_begin <= NOW()')
            ->where('registration_end >= NOW()');
        return new NDataSource($events);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumns([
            'event.name',
            'contest.contest',
        ]);
        $this->addButton('create')
            ->setText(_('Create application'))
            ->setLink(fn(ModelEvent $row): string => $this->getPresenter()
                ->link(':Public:Application:default', ['eventId' => $row->event_id]))
            ->setShow(function (ModelEvent $modelEvent): bool {
                $holder = $this->eventDispatchFactory->getDummyHolder($modelEvent);
                $machine = $this->eventDispatchFactory->getEventMachine($modelEvent);
                $transitions = $machine->getPrimaryMachine()->getAvailableTransitions(
                    $holder,
                    AbstractMachine::STATE_INIT,
                    true,
                    true
                );
                return (bool)count($transitions);
            });
    }
}
