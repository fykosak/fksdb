<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\ImportComponent;
use FKSDB\Components\Controls\Transition\AttendanceComponent;
use FKSDB\Components\Controls\Transition\MassTransitionsComponent;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\Grids\Application\SingleApplicationsGrid;
use FKSDB\Components\Schedule\PersonGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class ApplicationPresenter extends BasePresenter
{
    /** @use EventEntityPresenterTrait<EventParticipantModel> */
    use EventEntityPresenterTrait;

    protected EventParticipantService $eventParticipantService;

    public function injectServiceEventParticipant(EventParticipantService $eventParticipantService): void
    {
        $this->eventParticipantService = $eventParticipantService;
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(null, _('Application import'), 'fas fa-download');
    }

    /**
     * @throws EventNotFoundException
     */
    public function renderList(): void
    {
        $this->template->event = $this->getEvent();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws \Throwable
     */
    public function titleDetail(): PageTitle
    {
        $entity = $this->getEntity();
        return new PageTitle(
            null,
            sprintf(_('Application detail "%s"'), $entity->__toString()),
            'fas fa-user'
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedImport(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'import');
    }

    protected function getModelResource(): string
    {
        return EventParticipantModel::RESOURCE_ID;
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function renderDetail(): void
    {
        $this->template->event = $this->getEvent();
        $this->template->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
        $this->template->isOrg = $this->isAllowed('event.application', 'default');
        $this->template->fields = $this->getDummyHolder()->getFields();
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
        return !$this->getEvent()->isTeamEvent();
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedFastEdit(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'org-edit', $this->getEvent());
    }

    /**
     * @param EventParticipantModel|Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    public function titleAttendance(): PageTitle
    {
        return new PageTitle(null, _('Fast attendance'), 'fas fa-user-check');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedAttendance(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'org-edit', $this->getEvent());
    }

    public function titleMass(): PageTitle
    {
        return new PageTitle(null, _('Mass transitions'), 'fas fa-exchange-alt');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedMass(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'org-edit', $this->getEvent());
    }


    public function titleFastEdit(): PageTitle
    {
        return new PageTitle(null, _('Fast edit'), 'fas fa-pen');
    }

    /**
     * @throws EventNotFoundException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentGrid(): SingleApplicationsGrid
    {
        return new SingleApplicationsGrid($this->getEvent(), $this->getDummyHolder(), $this->getContext());
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of applications'), 'fas fa-address-book');
    }


    /**
     * @throws EventNotFoundException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentImport(): ImportComponent
    {
        return new ImportComponent($this->getContext(), $this->getEvent());
    }

    protected function getORMService(): EventParticipantService
    {
        return $this->eventParticipantService;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    public function getHolder(): BaseHolder
    {
        return $this->getMachine()->createHolder($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    protected function getMachine(): EventParticipantMachine
    {
        return $this->eventDispatchFactory->getEventMachine($this->getEvent());//@phpstan-ignore-line
    }

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     * @phpstan-return AttendanceComponent<BaseHolder>
     */
    protected function createComponentFastTransition(): AttendanceComponent
    {
        return new AttendanceComponent(
            $this->getContext(),
            $this->getEvent(),
            EventParticipantStatus::tryFrom(EventParticipantStatus::PAID),
            EventParticipantStatus::tryFrom(EventParticipantStatus::PARTICIPATED),
            $this->getMachine(),
        );
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    /**
     * @return TransitionButtonsComponent<BaseHolder>
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function createComponentApplicationTransitions(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getMachine(),
            $this->getHolder()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     * @phpstan-return MassTransitionsComponent<EventParticipantMachine>
     */
    protected function createComponentMassTransitions(): MassTransitionsComponent
    {
        return new MassTransitionsComponent($this->getContext(), $this->getMachine(), $this->getEvent());
    }


    protected function createComponentPersonScheduleGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext());
    }
}
