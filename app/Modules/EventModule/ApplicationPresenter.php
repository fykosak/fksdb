<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Model\Entity\ModelNotFoundException;
use FKSDB\Model\Events\Exceptions\EventNotFoundException;
use FKSDB\Model\Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Controls\Events\ImportComponent;
use FKSDB\Components\Grids\Application\AbstractApplicationsGrid;
use FKSDB\Components\Grids\Application\SingleApplicationsGrid;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\Logging\MemoryLogger;
use FKSDB\Model\ORM\Models\ModelEventParticipant;
use FKSDB\Model\ORM\Services\ServiceEventParticipant;
use FKSDB\Model\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ApplicationPresenter extends AbstractApplicationPresenter {
    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleImport(): void {
        $this->setPageTitle(new PageTitle(_('Application import'), 'fa fa-upload'));
    }

    /**
     * @return bool
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool {
        return !$this->isTeamEvent();
    }

    /**
     *
     * use same method of permissions as trait
     * @throws EventNotFoundException
     */
    public function authorizedImport(): void {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'import'));
    }

    /**
     * @return AbstractApplicationsGrid
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     */
    protected function createComponentGrid(): AbstractApplicationsGrid {
        return new SingleApplicationsGrid($this->getEvent(), $this->getHolder(), $this->getContext());
    }

    /**
     * @return ImportComponent
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     */
    protected function createComponentImport(): ImportComponent {
        $source = new SingleEventSource($this->getEvent(), $this->getContext(), $this->eventDispatchFactory);
        $machine = $this->eventDispatchFactory->getEventMachine($this->getEvent());
        $handler = $this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger());

        return new ImportComponent($machine, $source, $handler, $this->getContext());
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     */
    public function renderDetail(): void {
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

    protected function getORMService(): ServiceEventParticipant {
        return $this->serviceEventParticipant;
    }

    protected function getModelResource(): string {
        return ModelEventParticipant::RESOURCE_ID;
    }
}
