<?php

namespace EventModule;

use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ImportComponent;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class ApplicationPresenter
 * @package EventModule
 */
class ApplicationPresenter extends AbstractApplicationPresenter {
    /**
     * @var ServiceEventParticipant
     */
    private $serviceEventParticipant;

    /**
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function injectServiceEventParticipant(ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function titleList() {
        $this->setTitle(_('List of applications'));
        $this->setIcon('fa fa-users');
    }

    public function titleDetail() {
        $this->setTitle(_('Application detail'));
        $this->setIcon('fa fa-user');
    }

    public function titleImport() {
        $this->setTitle(_('Application import'));
        $this->setIcon('fa fa-upload');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDetail(int $id) {
        if ($this->isTeamEvent()) {
            $this->setAuthorized(false);
        } else {
            parent::authorizedDetail($id);
        }
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedImport() {
        if ($this->isTeamEvent()) {
            $this->setAuthorized(false);
        } else {
            $this->setAuthorized($this->eventIsAllowed($this->getModelResource(), 'import'));
        }
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedList() {
        if ($this->isTeamEvent()) {
            $this->setAuthorized(false);
        } else {
            parent::authorizedList();
        }
    }

    /**
     * @return ApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGrid(): AbstractApplicationGrid {
        return new ApplicationGrid($this->getEvent(), $this->getTableReflectionFactory());
    }

    /**
     * @return ImportComponent
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentImport(): ImportComponent {
        $source = new SingleEventSource($this->getEvent(), $this->container);
        $logger = new MemoryLogger();
        $machine = $this->container->createEventMachine($this->getEvent());
        $handler = $this->applicationHandlerFactory->create($this->getEvent(), $logger);

        $flashDump = $this->dumpFactory->create('application');
        return new ImportComponent($machine, $source, $handler, $flashDump, $this->container);
    }


    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function renderDetail() {
        parent::renderDetail();
        $this->template->fields = $this->getEvent()->getHolder()->getPrimaryHolder()->getFields();
        $this->template->model = $this->getEntity();
        $this->template->groups = [
            _('Health & food') => ['health_restrictions', 'diet', 'used_drugs', 'note', 'swimmer'],
            _('T-shirt') => ['tshirt_size', 'tshirt_color'],
            _('Arrival') => ['arrival_time', 'arrival_destination', 'arrival_ticket'],
            _('Departure') => ['departure_time', 'departure_destination', 'departure_ticket'],
        ];
    }

    /**
     * @return AbstractServiceSingle
     */
    function getORMService() {
        return $this->serviceEventParticipant;
    }

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return ModelEventParticipant::RESOURCE_ID;
    }
}
