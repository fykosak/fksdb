<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\YearCalculator;
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
     * @param ModelPerson $person
     * @param Container $container
     */
    public function __construct(ModelPerson $person, Container $container) {
        parent::__construct($container);
        $this->person = $person;
//TODO yearCalculator will be removed
        $this->yearCalculator = $container->getByType(YearCalculator::class);
    }

    /**
     * @param ServiceEvent $serviceEvent
     * @return void
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    protected function getData(): IDataSource {
        $events = $this->serviceEvent->getTable()->order('begin DESC');
        return new NDataSource($events);
    }

    /**
     * @param Presenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);
        $this->addColumns(['event.event_id', 'event.name', 'referenced.contest', 'event.year','event.role']);
        $this->addLinkButton('Dashboard:default', 'detail', _('Detail'), false, ['eventId' => 'event_id']);
    }

    protected function getModelClassName(): string {
        return ModelEvent::class;
    }
}
