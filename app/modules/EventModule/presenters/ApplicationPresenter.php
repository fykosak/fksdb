<?php

namespace EventModule;

use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ImportComponent;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class ApplicationPresenter
 * @package EventModule
 */
class ApplicationPresenter extends AbstractApplicationPresenter {

    public function titleImport() {
        $this->setTitle(_('Application import'), 'fa fa-upload');
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedImport() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'import'));
    }

    /**
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function isEnabledForEvent(): bool {
        return !$this->isTeamEvent();
    }

    /**
     * @return ApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGrid(): AbstractApplicationGrid {
        return new ApplicationGrid($this->getEvent(), $this->getContext());
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
        return new ImportComponent($machine, $source, $handler, $this->container);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        parent::renderDetail($id);
        $this->template->fields = $this->getEvent()->getHolder()->getPrimaryHolder()->getFields();

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
