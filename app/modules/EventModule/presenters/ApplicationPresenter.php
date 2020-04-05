<?php

namespace EventModule;

use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ImportComponent;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Events\Application\ApplicationGrid;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\Services\ServiceEventParticipant;
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
     * Same as
     */
    public function authorizedImport() {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'import'));
    }

    /**
     * @return bool
     * @throws BadRequestException
     * use same method of permissions as trait
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
     * @return AbstractServiceSingle|ServiceEventParticipant
     */
    function getORMService(): ServiceEventParticipant {
        return $this->serviceEventParticipant;
    }
}
