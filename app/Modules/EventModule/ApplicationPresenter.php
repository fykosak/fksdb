<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\ImportComponent;
use FKSDB\Components\Grids\Application\AbstractApplicationsGrid;
use FKSDB\Components\Grids\Application\SingleApplicationsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\Grid\SingleEventSource;
use FKSDB\Models\Expressions\NeonSchemaException;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;

class ApplicationPresenter extends AbstractApplicationPresenter
{

    public function titleImport(): PageTitle
    {
        return new PageTitle(_('Application import'), 'fas fa-download');
    }

    /**
     *
     * use same method of permissions as trait
     * @throws EventNotFoundException
     */
    public function authorizedImport(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'import'));
    }

    protected function getModelResource(): string
    {
        return ModelEventParticipant::RESOURCE_ID;
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     * @throws CannotAccessModelException
     */
    final public function renderDetail(): void
    {
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
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return !$this->isTeamEvent();
    }

    /**
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentGrid(): AbstractApplicationsGrid
    {
        return new SingleApplicationsGrid($this->getEvent(), $this->getHolder(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentImport(): ImportComponent
    {
        $source = new SingleEventSource($this->getEvent(), $this->getContext(), $this->eventDispatchFactory);
        $handler = new ApplicationHandler($this->getEvent(), new MemoryLogger(), $this->getContext());

        return new ImportComponent($source, $handler, $this->getContext());
    }

    protected function getORMService(): ServiceEventParticipant
    {
        return $this->serviceEventParticipant;
    }
}
