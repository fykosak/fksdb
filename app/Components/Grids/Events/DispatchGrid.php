<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class DispatchGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DispatchGrid extends BaseGrid {

    private ServiceEvent $serviceEvent;

    private ModelPerson $person;

    /**
     * DispatchGrid constructor.
     * @param ModelPerson $person
     * @param Container $container
     */
    public function __construct(ModelPerson $person, Container $container) {
        parent::__construct($container);
        $this->person = $person;
    }

    public function injectServiceEvent(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    protected function getData(): IDataSource {
        $events = $this->serviceEvent->getTable()->order('begin DESC');
        return new NDataSource($events);

    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->addColumns(['event.event_id', 'event.name', 'contest.contestBadge', 'event.year', 'event.role']);

        $this->addLinkButton('Dashboard:default', 'detail', _('Detail'), false, ['eventId' => 'event_id']);
    }

    protected function getModelClassName(): string {
        return ModelEvent::class;
    }
}
