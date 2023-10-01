<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\ImportComponent;
use FKSDB\Components\Controls\Transition\AttendanceComponent;
use FKSDB\Components\Controls\Transition\MassTransitionsComponent;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\Dsef\DsefFormComponent;
use FKSDB\Components\Grids\Application\SingleApplicationsGrid;
use FKSDB\Components\Schedule\PersonGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class ApplicationPresenter extends BasePresenter
{
    /** @use EventEntityPresenterTrait<EventParticipantModel> */
    use EventEntityPresenterTrait;

    protected EventParticipantService $eventParticipantService;

    public function injectServiceService(EventParticipantService $service): void
    {
        $this->eventParticipantService = $service;
    }

    /**
     * @param EventParticipantModel|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return !$this->getEvent()->isTeamEvent();
    }

    public function requiresLogin(): bool
    {
        return $this->getAction() !== 'create';
    }

    protected function getORMService(): EventParticipantService
    {
        return $this->eventParticipantService;
    }

    /**
     * @throws EventNotFoundException
     * @throws UnsupportedLanguageException
     */
    protected function startup(): void
    {
        if (in_array($this->getAction(), ['create', 'edit'])) {
            if (!in_array($this->getEvent()->event_type_id, [2, 14])) {
                $this->redirect(
                    ':Public:Application:default',
                    array_merge(['eventId' => $this->eventId], $this->getParameters())
                );
            }
        }
        parent::startup();
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedAttendance(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'organizer', $this->getEvent());
    }

    public function titleAttendance(): PageTitle
    {
        return new PageTitle(null, _('Fast attendance'), 'fas fa-user-check');
    }

    public function authorizedCreate(): bool
    {
        $event = $this->getEvent();
        return
            $this->eventAuthorizator->isAllowed(EventParticipantModel::RESOURCE_ID, 'organizer', $event) || (
                $event->isRegistrationOpened()
                && $this->eventAuthorizator->isAllowed(EventParticipantModel::RESOURCE_ID, 'create', $event)
            );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create application'), 'fas fa-plus');
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
        $this->template->isOrganizer = $this->isAllowed($this->getModelResource(), 'default');
        $this->template->fields = $this->getDummyHolder()->getFields();
        $this->template->model = $this->getEntity();
        $this->template->groups = [
            _('Health & food') => ['health_restrictions', 'diet', 'used_drugs', 'note', 'swimmer'],
            _('T-shirt') => ['tshirt_size', 'tshirt_color'],
            _('Arrival') => ['arrival_time', 'arrival_destination', 'arrival_ticket'],
            _('Departure') => ['departure_time', 'departure_destination', 'departure_ticket'],
            _('Food') => ['lunch_count'],
        ];
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
            sprintf(_('Application detail "%s"'), $entity->person->getFullName()),
            'fas fa-user'
        );
    }


    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): bool
    {
        $event = $this->getEvent();
        return $this->eventAuthorizator->isAllowed($this->getEntity(), 'organizer', $event) || (
                $event->isRegistrationOpened()
                && $this->eventAuthorizator->isAllowed($this->getEntity(), 'edit', $event));
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit application "%s"'), $this->getEntity()->person->getFullName()),
            'fas fa-edit'
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedFastEdit(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'organizer', $this->getEvent());
    }

    public function titleFastEdit(): PageTitle
    {
        return new PageTitle(null, _('Fast edit'), 'fas fa-pen');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedImport(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'import');
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

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of applications'), 'fas fa-address-book');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedMass(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'organizer', $this->getEvent());
    }

    public function titleMass(): PageTitle
    {
        return new PageTitle(null, _('Mass transitions'), 'fas fa-exchange-alt');
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     * @throws EventNotFoundException
     */
    private function getHolder(): BaseHolder
    {
        return $this->getMachine()->createHolder($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     */
    private function getMachine(): EventParticipantMachine
    {
        return $this->eventDispatchFactory->getParticipantMachine($this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentGrid(): SingleApplicationsGrid
    {
        return new SingleApplicationsGrid($this->getEvent(), $this->getDummyHolder(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentImport(): ImportComponent
    {
        return new ImportComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @phpstan-return AttendanceComponent<BaseHolder>
     */
    protected function createComponentFastTransition(): AttendanceComponent
    {
        return new AttendanceComponent(
            $this->getContext(),
            $this->getEvent(),
            EventParticipantStatus::from(EventParticipantStatus::PAID),
            EventParticipantStatus::from(EventParticipantStatus::PARTICIPATED),
            $this->getMachine(),
        );
    }

    /**
     * @throws NotImplementedException
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): DsefFormComponent
    {
        return $this->createForm(null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws NotImplementedException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): DsefFormComponent
    {
        return $this->createForm($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     */
    private function createForm(?EventParticipantModel $model): DsefFormComponent
    {
        switch ($this->getEvent()->event_type_id) {
            case 2:
            case 14:
                return new DsefFormComponent(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getMachine(),
                    $this->getLoggedPerson()
                );
        }
        throw new NotImplementedException();
    }

    /**
     * @phpstan-return TransitionButtonsComponent<BaseHolder>
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
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
