<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Event\MassTransition\MassTransitionComponent;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\Single\DsefFormComponent;
use FKSDB\Components\EntityForms\Single\SetkaniFormComponent;
use FKSDB\Components\EntityForms\Single\SingleFormComponent;
use FKSDB\Components\Event\Code\CodeComponent;
use FKSDB\Components\Event\Import\ImportComponent;
use FKSDB\Components\Grids\Application\SingleApplicationsGrid;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Components\Schedule\PersonScheduleGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidStateException;
use Nette\Security\Resource;

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
     * @param Resource|string|null $resource
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
            if (!in_array($this->getEvent()->event_type_id, [2, 14, 11, 12])) {
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
    public function authorizedCode(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModelResource(), 'organizer', $this->getEvent());
    }

    public function titleCode(): PageTitle
    {
        return new PageTitle(null, _('Scan 2D code'), 'fas fa-qrcode');
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
        $this->template->machineCode = $this->createMachineCode();
        $this->template->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
        $this->template->isOrganizer = $this->isAllowed($this->getModelResource(), 'default');
        switch ($this->getEvent()->event_type_id) {
            case 2:
            case 14:
                $this->template->fields = ['lunch_count'];
                break;
            case 11:
            case 12:
                $this->template->fields = ['diet', 'health_restrictions', 'note'];
                break;
            default:
                $this->template->fields = [];
        }
        $this->template->model = $this->getEntity();
        $this->template->groups = [
            _('Health & food') => ['health_restrictions', 'diet', 'used_drugs', 'note', 'swimmer'],
            _('T-shirt') => ['tshirt_size', 'tshirt_color'],
            _('Arrival') => ['arrival_time', 'arrival_destination', 'arrival_ticket'],
            _('Departure') => ['departure_time', 'departure_destination', 'departure_ticket'],
            _('Food') => ['lunch_count'],
            _('Price') => ['price'],
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
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    private function getHolder(): BaseHolder
    {
        return $this->getMachine()->createHolder($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    private function getMachine(): EventParticipantMachine
    {
        return $this->eventDispatchFactory->getParticipantMachine($this->getEvent());
    }

    private function createMachineCode(): ?string
    {
        try {
            return MachineCode::createHash($this->getEntity(), MachineCode::getSaltForEvent($this->getEvent()));
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * @throws EventNotFoundException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentGrid(): SingleApplicationsGrid
    {
        return new SingleApplicationsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): SingleFormComponent
    {
        return $this->createForm(null);
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): SingleFormComponent
    {
        return $this->createForm($this->getEntity());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    private function createForm(?EventParticipantModel $model): SingleFormComponent
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
            case 11:
            case 12:
                return new SetkaniFormComponent(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getMachine(),
                    $this->getLoggedPerson()
                );
        }
        throw new InvalidStateException(_('Event type is not supported'));
    }

    /**
     * @phpstan-return CodeComponent<BaseHolder>
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    protected function createComponentCode(): CodeComponent
    {
        return new CodeComponent(
            $this->getContext(),
            $this->getEvent(),
            EventParticipantStatus::from(EventParticipantStatus::PARTICIPATED),
            $this->getMachine(),
        );
    }

    /**
     * @phpstan-return TransitionButtonsComponent<BaseHolder>
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws EventNotFoundException
     * @throws BadTypeException
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
     * @phpstan-return MassTransitionComponent<EventParticipantMachine>
     */
    protected function createComponentMassTransitions(): MassTransitionComponent
    {
        return new MassTransitionComponent($this->getContext(), $this->getMachine(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentImport(): ImportComponent
    {
        return new ImportComponent($this->getContext(), $this->getEvent());
    }


    protected function createComponentPersonScheduleGrid(): PersonScheduleGrid
    {
        return new PersonScheduleGrid($this->getContext());
    }
}
