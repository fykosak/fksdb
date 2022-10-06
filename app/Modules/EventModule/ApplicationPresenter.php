<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\MassTransitionsComponent;
use FKSDB\Components\Grids\Application\SingleApplicationsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

class ApplicationPresenter extends AbstractApplicationPresenter
{

    public function titleImport(): PageTitle
    {
        return new PageTitle(null, _('Application import'), 'fas fa-download');
    }

    /**
     *
     * use same method of permissions as trait
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedImport(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'import'));
    }

    protected function getModelResource(): string
    {
        return EventParticipantModel::RESOURCE_ID;
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderDetail(): void
    {
        parent::renderDetail();
        $this->getTemplate()->fields = $this->getDummyHolder()->getFields();
        $this->getTemplate()->model = $this->getEntity();
        $this->getTemplate()->groups = [
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
        return !$this->getEvent()->isTeamEvent();
    }

    /**
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentGrid(): SingleApplicationsGrid
    {
        return new SingleApplicationsGrid($this->getEvent(), $this->getDummyHolder(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     */
    final protected function createComponentMassTransitions(): MassTransitionsComponent
    {
        return new MassTransitionsComponent($this->getContext(), $this->getEvent());
    }

    protected function getORMService(): EventParticipantService
    {
        return $this->eventParticipantService;
    }
}
