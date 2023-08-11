<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\FastEditComponent;
use FKSDB\Components\Controls\SchoolCheckComponent;
use FKSDB\Components\Controls\Transition\AttendanceComponent;
use FKSDB\Components\Controls\Transition\MassTransitionsComponent;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\EntityForms\Fyziklani\FOFTeamFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\FOLTeamFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\TeamFormComponent;
use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Grids\Application\TeamApplicationsGrid;
use FKSDB\Components\Grids\Application\TeamListComponent;
use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\PageComponent;
use FKSDB\Components\Schedule\PersonGrid;
use FKSDB\Components\Schedule\Rests\TeamRestsComponent;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidStateException;

final class TeamApplicationPresenter extends BasePresenter
{
    /** @use EventEntityPresenterTrait<TeamModel2> */
    use EventEntityPresenterTrait;

    private TeamService2 $teamService;

    public function injectServiceFyziklaniTeam(TeamService2 $teamService): void
    {
        $this->teamService = $teamService;
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create team'), 'fas fa-calendar-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of teams'), 'fas fa-address-book');
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
     * @param TeamModel2|string|null $resource
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
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws \Throwable
     */
    public function titleDetail(): PageTitle
    {
        $entity = $this->getEntity();
        return new PageTitle(
            null,
            sprintf(_('Application detail "%s"'), $entity->name),
            'fas fa-user'
        );
    }

    public function authorizedCreate(): bool
    {
        $event = $this->getEvent();
        return
            $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'org-create', $event) || (
                $event->isRegistrationOpened()
                && $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'create', $event)
            );
    }

    public function titleDetailedList(): PageTitle
    {
        return new PageTitle(null, _('Detailed list of teams'), 'fas fa-address-book');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDetailedList(): bool
    {
        return $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'list', $this->getEvent());
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
        return new PageTitle(null, sprintf(_('Edit team "%s"'), $this->getEntity()->name), 'fas fa-edit');
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
        return $this->eventAuthorizator->isAllowed($this->getEntity(), 'org-edit', $event) || (
                $event->isRegistrationOpened()
                && $this->eventAuthorizator->isAllowed($this->getEntity(), 'edit', $event));
    }

    public function requiresLogin(): bool
    {
        return $this->getAction() !== 'create';
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    public function getHolder(): TeamHolder
    {
        return $this->getMachine()->createHolder($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    protected function getMachine(): TeamMachine
    {
        return $this->eventDispatchFactory->getEventMachine($this->getEvent()); //@phpstan-ignore-line
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
        try {
            $setup = $this->getEvent()->getGameSetup();
            $rankVisible = $setup->result_hard_display;
        } catch (NotSetGameParametersException $exception) {
            $rankVisible = false;
        }
        $this->template->isOrg = $this->eventAuthorizator->isAllowed(
            $this->getEntity(),
            'org-detail',
            $this->getEvent()
        );
        $this->template->rankVisible = $rankVisible;
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return $this->getEvent()->isTeamEvent();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @phpstan-return ProviderComponent<TeamModel2,array<never>>
     */
    protected function createComponentSeating(): ProviderComponent
    {
        return new ProviderComponent(
            new PageComponent($this->getContext()),
            [$this->getEntity()],
            $this->getContext()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentSchoolCheck(): SchoolCheckComponent
    {
        return new SchoolCheckComponent($this->getEvent(), $this->getContext());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): TeamFormComponent
    {
        return $this->createTeamForm(null);
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): TeamFormComponent
    {
        return $this->createTeamForm($this->getEntity());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    private function createTeamForm(?TeamModel2 $model): TeamFormComponent
    {
        switch ($this->getEvent()->event_type_id) {
            case 1:
                return new FOFTeamFormComponent(
                    $this->getMachine(),
                    $this->getEvent(),
                    $this->getContext(),
                    $model
                );
            case 9:
                return new FOLTeamFormComponent(
                    $this->getMachine(),
                    $this->getEvent(),
                    $this->getContext(),
                    $model
                );
        }
        throw new InvalidStateException(_('Event type is not supported'));
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TeamApplicationsGrid
    {
        return new TeamApplicationsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentList(): TeamListComponent
    {
        return new TeamListComponent($this->getEvent(), $this->getContext());
    }

    protected function createComponentTeamRestsControl(): TeamRestsComponent
    {
        return new TeamRestsComponent($this->getContext());
    }

    protected function getORMService(): TeamService2
    {
        return $this->teamService;
    }

    /**
     * @return AttendanceComponent<TeamHolder>
     * @throws EventNotFoundException|BadTypeException
     */
    protected function createComponentFastTransition(): AttendanceComponent
    {
        return new AttendanceComponent(
            $this->getContext(),
            $this->getEvent(),
            TeamState::from(TeamState::APPROVED),
            TeamState::from(TeamState::PARTICIPATED),
            $this->getMachine()
        );
    }

    protected function createComponentFastEdit(): FastEditComponent
    {
        return new FastEditComponent($this->getContext());
    }

    /**
     * @return TransitionButtonsComponent<TeamHolder>
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
     * @phpstan-return MassTransitionsComponent<TeamMachine>
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
