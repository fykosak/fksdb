<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Applications\Team\Forms\FOFTeamForm;
use FKSDB\Components\Applications\Team\Forms\FOLTeamForm;
use FKSDB\Components\Applications\Team\Forms\TeamForm;
use FKSDB\Components\Applications\Team\NoteForm;
use FKSDB\Components\Applications\Team\SchoolCheckComponent;
use FKSDB\Components\Applications\Team\TeamGrid;
use FKSDB\Components\Applications\Team\TeamList;
use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Components\Event\MassTransition\MassTransitionComponent;
use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\Seating\Single;
use FKSDB\Components\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\Schedule\SinglePersonGrid;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class TeamPresenter extends BasePresenter
{
    /** @use EventEntityPresenterTrait<TeamModel2> */
    use EventEntityPresenterTrait;

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

    /**
     * @throws GoneException
     */
    protected function getORMService(): TeamService2
    {
        throw new GoneException();
    }

    protected function loadModel(): TeamModel2
    {
        /** @var TeamModel2|null $candidate */
        $candidate = $this->getEvent()->getTeams()->where('fyziklani_team_id', $this->id)->fetch();
        if ($candidate) {
            return $candidate;
        } else {
            throw new NotFoundException(_('Model does not exist.'));
        }
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(TeamModel2::RESOURCE_ID, $this->getEvent()),
            'create',
            $this->getEvent()
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create team'), 'fas fa-calendar-plus');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     * @throws ForbiddenRequestException
     * @throws EventNotFoundException
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEntity()),
            'detail',
            $this->getEvent()
        );
    }
    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
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
     * @throws NotFoundException
     * @throws \Throwable
     */
    public function titleDetail(): PageTitle
    {
        $entity = $this->getEntity();
        return new PageTitle(
            null,
            Html::el('span')
                ->addText(sprintf(_('Team: %s'), $entity->name))
                ->addHtml(
                    Html::el('small')
                        ->addAttributes(['class' => 'ms-2'])
                        ->addHtml($entity->state->pseudoState()->badge())
                ),
            'fas fa-user'
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function authorizedOrgDetail(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEntity()),
            'organizerDetail',
            $this->getEvent()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function renderOrgDetail(): void
    {
        $this->renderDetail();
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws \Throwable
     */
    public function titleOrgDetail(): PageTitle
    {
        $entity = $this->getEntity();
        return new PageTitle(
            null,
            Html::el('span')
                ->addText(sprintf(_('Team: %s'), $entity->name))
                ->addHtml(Html::el('small')->addAttributes(['class' => 'ms-2'])->addHtml($entity->state->badge())),
            'fas fa-user'
        );
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDetailedList(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(TeamModel2::RESOURCE_ID, $this->getEvent()),
            'list',
            $this->getEvent()
        );
    }

    public function titleDetailedList(): PageTitle
    {
        return new PageTitle(null, _('Detailed list of teams'), 'fas fa-address-book');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEntity()),
            'edit',
            $this->getEvent()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
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
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(TeamModel2::RESOURCE_ID, $this->getEvent()),
            'list',
            $this->getEvent()
        );
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Teams'), 'fas fa-address-book');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedMass(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(TeamModel2::RESOURCE_ID, $this->getEvent()),
            'mass',
            $this->getEvent()
        );
    }

    public function titleMass(): PageTitle
    {
        return new PageTitle(null, _('Mass transitions'), 'fas fa-exchange-alt');
    }

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
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
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): TeamForm
    {
        return $this->createForm(null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): TeamForm
    {
        return $this->createForm($this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     */
    private function createForm(?TeamModel2 $model): TeamForm
    {
        switch ($this->getEvent()->event_type_id) {
            case 1:
                return new FOFTeamForm(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getLoggedPerson()
                );
            case 9:
                return new FOLTeamForm(
                    $this->getContext(),
                    $model,
                    $this->getEvent(),
                    $this->getLoggedPerson()
                );
        }
        throw new InvalidStateException(_('Event type is not supported'));
    }

    /**
     * @phpstan-return TransitionButtonsComponent<TeamModel2>
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws EventNotFoundException
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
     * @phpstan-return MassTransitionComponent<TeamModel2>
     */
    protected function createComponentMassTransition(): MassTransitionComponent
    {
        /** @phpstan-ignore-next-line */
        return new MassTransitionComponent($this->getContext(), $this->getMachine(), $this->getEvent()->getTeams());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentRests(): TeamRestsComponent
    {
        return new TeamRestsComponent($this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentSeating(): Single
    {
        return new Single($this->getContext(), $this->getEntity());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentSchoolCheck(): SchoolCheckComponent
    {
        return new SchoolCheckComponent($this->getEntity(), $this->getContext());
    }

    /**
     * @phpstan-return TestsList<TeamModel2>
     */
    protected function createComponentTests(): TestsList
    {
        return new TestsList($this->getContext(), DataTestFactory::getTeamTests($this->getContext()), true);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    protected function createComponentNoteForm(): NoteForm
    {
        return new NoteForm($this->getContext(), $this->getEntity());
    }
}
