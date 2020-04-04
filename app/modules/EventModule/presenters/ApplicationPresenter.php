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
     * @return bool
     * @throws BadRequestException
     */
    protected function isEnabled(): bool {
        return !$this->isTeamEvent();
    }

    /**
     * @throws BadRequestException
     * use same method of permissions as trait
     */
    public function authorizedImport() {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'import'));
    }

    /**
     * @return ApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGrid(): AbstractApplicationGrid {
        return new ApplicationGrid($this->getEvent(), $this->getHolder(), $this->getContext());
    }

    /**
     * @return ImportComponent
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentImport(): ImportComponent {
        $source = new SingleEventSource($this->getEvent(), $this->getContext());
        $machine = $this->getContext()->createEventMachine($this->getEvent());
        $handler = $this->applicationHandlerFactory->create($this->getEvent(),  new MemoryLogger());

        return new ImportComponent($machine, $source, $handler, $this->getContext());
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function renderDetail() {
        parent::renderDetail();
        $this->template->fields = $this->getHolder()->getPrimaryHolder()->getFields();
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
