<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\SchoolCheckComponent;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\DataTest\SingleTestComponent;
use FKSDB\Components\DataTest\Tests\PersonHistory\StudyTypeTest;
use FKSDB\Components\EntityForms\Fyziklani\FOFTeamFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\FOLTeamFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\TeamFormComponent;
use FKSDB\Components\Event\Code\CodeRedirectComponent;
use FKSDB\Components\Event\CodeTransition\CodeTransitionComponent;
use FKSDB\Components\Event\MassTransition\MassTransitionComponent;
use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Grids\Application\TeamGrid;
use FKSDB\Components\Grids\Application\TeamList;
use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\PageComponent;
use FKSDB\Components\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\Schedule\SinglePersonGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidStateException;
use Nette\Security\Resource;

final class TeamPresenter extends BasePresenter
{
    /** @use EventEntityPresenterTrait<TeamModel2> */
    use EventEntityPresenterTrait;

    private TeamService2 $teamService;

    public function injectServiceService(TeamService2 $service): void
    {
        $this->teamService = $service;
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
        return $this->getEvent()->isTeamEvent();
    }

    public function requiresLogin(): bool
    {
        return $this->getAction() !== 'create';
    }

    protected function getORMService(): TeamService2
    {
        return $this->teamService;
    }

    public function authorizedCreate(): bool
    {
        $event = $this->getEvent();
        return
            $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'organizer', $event) || (
                $event->isRegistrationOpened()
                && $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'create', $event)
            );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create team'), 'fas fa-calendar-plus');
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
        foreach ($this->getEntity()->getPersons() as $person) {
            $this->addComponent(
                new SinglePersonGrid($this->getContext(), $person, $this->getEvent()),
                'personSchedule' . $person->person_id
            );
        }

        $this->template->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
        $this->template->isOrganizer = $this->isAllowed($this->getModelResource(), 'organizer');
        try {
            $setup = $this->getEvent()->getGameSetup();
            $rankVisible = $setup->result_hard_display;
        } catch (NotSetGameParametersException $exception) {
            $rankVisible = false;
        }
        $this->template->rankVisible = $rankVisible;
        $this->template->model = $this->getEntity();
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
            sprintf(_('Team: %s'), $entity->name),
            'fas fa-user'
        );
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDetailedList(): bool
    {
        return $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'list', $this->getEvent());
    }

    public function titleDetailedList(): PageTitle
    {
        return new PageTitle(null, _('Detailed list of teams'), 'fas fa-address-book');
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
        return new PageTitle(null, sprintf(_('Edit team: %s'), $this->getEntity()->name), 'fas fa-edit');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->eventAuthorizator->isAllowed(TeamModel2::RESOURCE_ID, 'list', $this->getEvent());
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Teams'), 'fas fa-address-book');
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
    private function getHolder(): TeamHolder
    {
        return $this->getMachine()->createHolder($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    private function getMachine(): TeamMachine
    {
        return $this->eventDispatchFactory->getTeamMachine($this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TeamGrid
    {
        return new TeamGrid($this->getEvent(), $this->getContext());
    }
    /**
     * @throws EventNotFoundException
     */
    protected function createComponentList(): TeamList
    {
        return new TeamList($this->getEvent(), $this->getContext());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): TeamFormComponent
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
    protected function createComponentEditForm(): TeamFormComponent
    {
        return $this->createForm($this->getEntity());
    }

    /**
     * @throws BadTypeException
     * @throws EventNotFoundException
     */
    private function createForm(?TeamModel2 $model): TeamFormComponent
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
    protected function createComponentCode(): CodeRedirectComponent
    {
        return new CodeRedirectComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @phpstan-return TransitionButtonsComponent<TeamHolder>
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getMachine(),
            $this->getHolder()
        );
    }

    /**
     * @phpstan-return CodeTransitionComponent<TeamModel2>
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    protected function createComponentCodeTransition(): CodeTransitionComponent
    {
        return new CodeTransitionComponent(
            $this->getContext(),
            $this->getEntity(),
            TeamState::tryFrom(TeamState::Arrived), // TODO
            $this->getMachine()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     * @phpstan-return MassTransitionComponent<TeamMachine>
     */
    protected function createComponentMassTransition(): MassTransitionComponent
    {
        return new MassTransitionComponent($this->getContext(), $this->getMachine(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentRests(): TeamRestsComponent
    {
        return new TeamRestsComponent($this->getContext(), $this->getEntity());
    }

    /**
     * @phpstan-return SingleTestComponent<PersonHistoryModel>
     */
    protected function createComponentStudySchoolTest(): SingleTestComponent
    {
        return new SingleTestComponent($this->getContext(), new StudyTypeTest($this->getContext()));
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
}
