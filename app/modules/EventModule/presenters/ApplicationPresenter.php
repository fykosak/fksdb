<?php

namespace EventModule;

use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ImportComponent;
use FKSDB\Components\Grids\Events\Application\AbstractOrgApplicationsGrid;
use FKSDB\Components\Grids\Events\Application\OrgApplicationsGrid;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

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
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDetail() {
        if ($this->isTeamEvent()) {
            $this->setAuthorized(false);
        } else {
            $this->setAuthorized($this->eventIsAllowed('event.application', 'detail'));
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
            $this->setAuthorized($this->eventIsAllowed('event.application', 'import'));
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
            $this->setAuthorized($this->eventIsAllowed('event.application', 'list'));
        }
    }

    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws AbortException
     */
    protected function loadModel(int $id) {
        $row = $this->serviceEventParticipant->findByPrimary($id);
        if (!$row) {
            $this->flashMessage(_('Application not found'));
            $this->redirect('list');
        }
        $model = ModelEventParticipant::createFromActiveRow($row);
        if ($model->event_id != $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }
        $this->model = $model;
    }

    /**
     * @return ModelEventParticipant
     */
    protected function getModel(): ModelEventParticipant {
        return $this->model;
    }

    /**
     * @return OrgApplicationsGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGrid(): AbstractOrgApplicationsGrid {
        return new OrgApplicationsGrid($this->getEvent(), $this->getTableReflectionFactory());
    }

    /**
     * @return ImportComponent
     * @throws BadRequestException
     * @throws AbortException
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
        $this->template->fields = $this->getEvent()->getHolder()->getPrimaryHolder()->getFields();
        $this->template->model = $this->getModel();
        $this->template->groups = [
            _('Health & food') => ['health_restrictions', 'diet', 'used_drugs', 'note', 'swimmer'],
            _('T-shirt') => ['tshirt_size', 'tshirt_color'],
            _('Arrival') => ['arrival_time', 'arrival_destination', 'arrival_ticket'],
            _('Departure') => ['departure_time', 'departure_destination', 'departure_ticket'],
        ];
    }
}
