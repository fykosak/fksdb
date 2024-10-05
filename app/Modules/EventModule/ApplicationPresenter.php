<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Applications\Single\InvitedForms\SousForm;
use FKSDB\Components\Applications\Single\OpenForms\DSEFForm;
use FKSDB\Components\Applications\Single\OpenForms\SetkaniForm;
use FKSDB\Components\Applications\Single\OpenForms\TaborForm;
use FKSDB\Components\Applications\Single\SingleApplicationsGrid;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\Event\Import\ImportComponent;
use FKSDB\Components\Event\MassTransition\MassTransitionComponent;
use FKSDB\Components\Schedule\Rests\PersonRestComponent;
use FKSDB\Components\Schedule\SinglePersonGrid;
use FKSDB\Models\Authorization\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\BaseComponent\BaseComponent;
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
        return $this->eventAuthorizator->isAllowed(
            new PseudoEventResource(EventParticipantModel::RESOURCE_ID, $this->getEvent()),
            'create',
            $this->getEvent()
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
        return $this->eventAuthorizator->isAllowed($this->getEntity(), 'edit', $this->getEvent());
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
     */
    protected function createComponentGrid(): SingleApplicationsGrid
    {
        return new SingleApplicationsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
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
     */
    protected function createComponentEditForm(): BaseComponent
    {
        return $this->createForm($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     */
    private function createForm(?EventParticipantModel $model): BaseComponent
    {
        switch ($this->getEvent()->event_type_id) {
            case 4:
            case 5:
                return new SousForm(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getLoggedPerson()
                );
            case 2:
            case 14:
                return new DSEFForm(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getLoggedPerson()
                );
            case 10:
                return new TaborForm(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getLoggedPerson()
                );
            case 11:
            case 12:
                return new SetkaniForm(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
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
            $this->eventDispatchFactory->getParticipantMachine($this->getEvent()), // @phpstan-ignore-line
            $this->getEntity()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     * @phpstan-return MassTransitionComponent<EventParticipantModel>
     */
    protected function createComponentMassTransition(): MassTransitionComponent
    {
        return new MassTransitionComponent(
            $this->getContext(),
            /** @phpstan-ignore-next-line */
            $this->eventDispatchFactory->getParticipantMachine($this->getEvent()),
            $this->getEvent()->getParticipants()
        );
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
