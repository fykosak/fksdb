<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Components\Event\CodeAttendance\CodeAttendance;
use FKSDB\Components\Event\CodeSearch\CodeSearch;
use FKSDB\Components\Schedule\Rests\PersonRestComponent;
use FKSDB\Components\Schedule\Rests\TeamRestsComponent;
use FKSDB\Components\TeamSeating\Single;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;

/**
 * @phpstan-import-type TSupportedModel from MachineCode
 */
class AttendancePresenter extends BasePresenter
{
    /** @persistent */
    public ?int $id = null;

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->eventAuthorizator->isAllowed($this->getModel(), 'organizer', $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    public function renderDefault(): void
    {
        $this->template->model = $this->getModel();
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    public function titleDefault(): PageTitle
    {
        $model = $this->getModel();
        return new PageTitle(
            null,
            '(' . $model->getPrimary() . ') ' .
            ($model instanceof TeamModel2 ? $model->name : $model->person->getFullName()),
            'fas fa'
        );
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedSearch(): bool
    {
        return $this->eventAuthorizator->isAllowed(
            $this->getEvent()->isTeamEvent()
                ? TeamModel2::RESOURCE_ID
                : EventParticipantModel::RESOURCE_ID,
            'organizer',
            $this->getEvent()
        );
    }

    public function titleSearch(): PageTitle
    {
        return new PageTitle(null, _('Search by code'), 'fas fa');
    }

    /**
     * @throws EventNotFoundException
     * @throws MachineCodeException
     */
    protected function createComponentSearch(): CodeSearch
    {
        return new CodeSearch(
            $this->getContext(),
            /** @phpstan-param TSupportedModel $model */
            function (Model $model): void {
                if ($model instanceof TeamModel2) {
                    $application = $model;
                } elseif ($model instanceof PersonModel) {
                    $application = $model->getEventParticipant($this->getEvent());
                } else {
                    throw new BadRequestException(_('Wrong type of code.'));
                }
                if ($application->event_id !== $this->getEvent()->event_id) {
                    throw new BadRequestException(_('Application belongs to another event.'));
                }
                $this->redirect('default', ['id' => $application->getPrimary()]);
            },
            $this->getEvent()->getSalt()
        );
    }

    /**
     * @phpstan-return CodeAttendance<TeamModel2|EventParticipantModel>
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     */
    protected function createComponentAttendance(): CodeAttendance
    {
        return new CodeAttendance(
            $this->getContext(),
            $this->getModel(),
            $this->getEvent()->isTeamEvent()
                ? TeamState::tryFrom(TeamState::Arrived)
                : EventParticipantStatus::from(EventParticipantStatus::PARTICIPATED),
            $this->getMachine() //@phpstan-ignore-line
        );
    }

    /**
     * @return TeamModel2|EventParticipantModel
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    private function getModel(): Model
    {
        static $model;
        if (isset($this->id) && !isset($model)) {
            if ($this->getEvent()->isTeamEvent()) {
                $model = $this->getEvent()
                    ->getTeams()
                    ->where('fyziklani_team_id', $this->id)
                    ->fetch();
            } else {
                $model = $model = $this->getEvent()
                    ->getParticipants()
                    ->where('event_participant_id', $this->id)
                    ->fetch();
            }
        }
        if (!$model) {
            throw new NotFoundException();
        }
        return $model;
    }

    /**
     * @phpstan-return TransitionButtonsComponent<TeamModel2|EventParticipantModel>
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->getMachine(), // @phpstan-ignore-line
            $this->getModel()
        );
    }

    /**
     * @throws EventNotFoundException
     * @phpstan-return TeamMachine|Machine<ParticipantHolder>
     */
    private function getMachine(): Machine
    {
        return $this->getEvent()->isTeamEvent() //@phpstan-ignore-line
            ? $this->eventDispatchFactory->getTeamMachine($this->getEvent())
            : $this->eventDispatchFactory->getParticipantMachine($this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    protected function createComponentRests(): Control
    {
        $model = $this->getModel();
        if ($model instanceof TeamModel2) {
            return new TeamRestsComponent($this->getContext(), $model);
        } else {
            return new PersonRestComponent($this->getContext(), $model);
        }
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    protected function createComponentSeating(): Single
    {
        return new Single($this->getContext(), $this->getModel());//@phpstan-ignore-line
    }

    /**
     * @phpstan-return TestsList<TeamModel2>|TestsList<Model>
     * @throws EventNotFoundException
     */
    protected function createComponentTests(): TestsList
    {
        if ($this->getEvent()->isTeamEvent()) {
            return new TestsList($this->getContext(), DataTestFactory::getTeamTests($this->getContext()));
        } else {
            return new TestsList($this->getContext(), []);
        }
    }
}
