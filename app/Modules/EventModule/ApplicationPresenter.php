<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\SousApplicationForm;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\Single\DsefFormComponent;
use FKSDB\Components\EntityForms\Single\SetkaniFormComponent;
use FKSDB\Components\EntityForms\Single\TaborFormComponent;
use FKSDB\Components\Event\Import\ImportComponent;
use FKSDB\Components\Event\MassTransition\MassTransitionComponent;
use FKSDB\Components\Grids\Application\SingleApplicationsGrid;
use FKSDB\Components\Schedule\Rests\PersonRestComponent;
use FKSDB\Components\Schedule\SinglePersonGrid;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidStateException;
use Nette\Security\Resource;
use Nette\Utils\Html;

final class ApplicationPresenter extends BasePresenter
{
    /** @use EventEntityPresenterTrait<EventParticipantModel> */
    use EventEntityPresenterTrait;

    protected EventParticipantService $eventParticipantService;

    public function injectService(EventParticipantService $service): void
    {
        $this->eventParticipantService = $service;
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $this->getEvent());
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

    public function authorizedCreate(): bool
    {
        $event = $this->getEvent();
        if ($event->event_type_id === 10) {
            return $this->eventAuthorizator->isAllowed(EventParticipantModel::RESOURCE_ID, 'organizer', $event);
        }
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
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    public function renderDetail(): void
    {
        $this->template->isOrganizer = $this->eventAuthorizator->isAllowed(
            $this->getModelResource(),
            'organizer',
            $this->getEvent()
        );
        switch ($this->getEvent()->event_type_id) {
            case 10:
                $this->template->fields = ['diet', 'health_restrictions', 'used_drugs', 'note', 'swimmer'];
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
     * @throws \Throwable
     */
    public function titleDetail(): PageTitle
    {
        $entity = $this->getEntity();
        return new PageTitle(
            null,
            Html::el('span')
                ->addText(sprintf(_('Application: %s'), $entity->person->getFullName()))
                ->addText(' ')
                ->addHtml(Html::el('small')->addAttributes(['class' => 'ms-2'])->addHtml($entity->status->badge())),
            'fas fa-user'
        );
    }


    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NotFoundException
     * @throws NotFoundException
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
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    public function renderEdit(): void
    {
        $this->template->isOrganizer = $this->eventAuthorizator->isAllowed(
            $this->getModelResource(),
            'organizer',
            $this->getEvent()
        );
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit application: %s'), $this->getEntity()->person->getFullName()),
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

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Applications'), 'fas fa-address-book');
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
     * @throws EventNotFoundException
     * @throws NotImplementedException
     * @phpstan-return EventParticipantMachine
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
        return new SingleApplicationsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): BaseComponent
    {
        return $this->createForm(null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): BaseComponent
    {
        return $this->createForm($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     */
    private function createForm(?EventParticipantModel $model): BaseComponent
    {
        switch ($this->getEvent()->event_type_id) {
            case 4:
            case 5:
                return new SousApplicationForm(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getMachine(),
                    $this->getLoggedPerson()
                );
            case 2:
            case 14:
                return new DsefFormComponent(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getMachine(),
                    $this->getLoggedPerson()
                );
            case 10:
                return new TaborFormComponent(
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
     * @phpstan-return TransitionButtonsComponent<EventParticipantModel>
     * @throws ForbiddenRequestException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws NotImplementedException
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getMachine(), // @phpstan-ignore-line
            $this->getEntity()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     * @phpstan-return MassTransitionComponent<EventParticipantMachine>
     */
    protected function createComponentMassTransition(): MassTransitionComponent
    {
        return new MassTransitionComponent($this->getContext(), $this->getMachine(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    protected function createComponentRests(): PersonRestComponent
    {
        return new PersonRestComponent($this->getContext(), $this->getEntity());
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
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    protected function createComponentPersonScheduleGrid(): SinglePersonGrid
    {
        return new SinglePersonGrid($this->getContext(), $this->getEntity()->person, $this->getEvent());
    }
}
