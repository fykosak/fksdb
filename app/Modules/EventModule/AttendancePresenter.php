<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Components\Event\CodeAttendance\CodeAttendance;
use FKSDB\Components\Event\CodeSearch\CodeSearch;
use FKSDB\Components\Game\Seating\Single;
use FKSDB\Components\Schedule\Rests\PersonRestComponent;
use FKSDB\Components\Schedule\Rests\TeamRestsComponent;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\InvalidStateException;
use Nette\Utils\Html;

/**
 * @phpstan-template TTeamEvent of bool
 * @phpstan-import-type TSupportedModel from MachineCode
 */
final class AttendancePresenter extends BasePresenter
{
    /** @persistent */
    public ?int $id = null;

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedEvent($this->getModel(), 'attendance', $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    public function renderDetail(): void
    {
        $this->template->model = $this->getModel();
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     */
    public function titleDetail(): PageTitle
    {
        $model = $this->getModel();
        if ($model instanceof TeamModel2) {
            return new PageTitle(
                null,
                Html::el('span')
                    ->addText(sprintf('(%s) %s', $model->fyziklani_team_id, $model->name))
                    ->addHtml(Html::el('small')->addAttributes(['class' => 'ms-2'])->addHtml($model->state->badge())),
                'fas fa-door-open'
            );
        } else {
            return new PageTitle(
                null,
                Html::el('span')
                    ->addText(sprintf('(%s) %s', $model->event_participant_id, $model->person->getFullName()))
                    ->addHtml(Html::el('small')->addAttributes(['class' => 'ms-2'])->addHtml($model->status->badge())),
                'fas fa-door-open'
            );
        }
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedSearch(): bool
    {
        if ($this->getEvent()->isTeamEvent()) {
            return $this->authorizator->isAllowedEvent(
                new PseudoEventResource(TeamModel2::RESOURCE_ID, $this->getEvent()),
                'attendance',
                $this->getEvent()
            );
        } else {
            return $this->authorizator->isAllowedEvent(
                new PseudoEventResource(EventParticipantModel::RESOURCE_ID, $this->getEvent()),
                'attendance',
                $this->getEvent()
            );
        }
    }

    public function titleSearch(): PageTitle
    {
        return new PageTitle(null, 'Prezence', 'fas fa-door-open');
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
                if ($model instanceof TeamModel2 && $this->getEvent()->isTeamEvent()) {
                    $application = $model;
                } elseif ($model instanceof PersonModel && !$this->getEvent()->isTeamEvent()) {
                    $application = $model->getEventParticipant($this->getEvent());
                } else {
                    throw new BadRequestException(_('Wrong type of code.'));
                }
                if (!$application) {
                    throw new BadRequestException(_('Application not found.'));
                }
                if ($application->event_id !== $this->getEvent()->event_id) {
                    throw new BadRequestException(_('Application belongs to another event.'));
                }
                $this->redirect('detail', ['id' => $application->getPrimary()]);
            },
            $this->getEvent()->getSalt()
        );
    }

    /**
     * @phpstan-return (TTeamEvent is true?CodeAttendance<TeamModel2>:CodeAttendance<EventParticipantModel>)
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     * @throws NotImplementedException
     */
    protected function createComponentAttendance(): CodeAttendance
    {
        $model = $this->getModel();
        if ($model instanceof TeamModel2) {
            return new CodeAttendance(
                $this->getContext(),
                $model,
                TeamState::from(TeamState::Arrived),
                $this->getMachine() //@phpstan-ignore-line
            );
        } else {
            return new CodeAttendance(
                $this->getContext(),
                $model,
                EventParticipantStatus::from(EventParticipantStatus::PARTICIPATED),
                $this->getMachine() //@phpstan-ignore-line
            );
        }
    }

    /**
     * @phpstan-return (TTeamEvent is true?TeamModel2:EventParticipantModel)
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
     * @phpstan-return (TTeamEvent is true
     * ?TransitionButtonsComponent<TeamModel2>
     * :TransitionButtonsComponent<EventParticipantModel>
     * )
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     * @throws NotImplementedException
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(// @phpstan-ignore-line
            $this->getContext(),
            $this->getMachine(), // @phpstan-ignore-line
            $this->getModel()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     * @phpstan-return (TTeamEvent is true
     * ?\FKSDB\Models\Transitions\Machine\TeamMachine
     * :Machine<\FKSDB\Models\Transitions\Holder\ParticipantHolder>)
     */
    private function getMachine(): Machine
    {
        return $this->eventDispatchFactory->getEventMachine($this->getEvent());
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
        $model = $this->getModel();
        if ($model instanceof TeamModel2) {
            return new Single($this->getContext(), $model);
        }
        throw new InvalidStateException();
    }

    /**
     * @phpstan-return (TTeamEvent is true?
     * TestsList<TeamModel2>:
     * TestsList<never>)
     * @throws EventNotFoundException
     */
    protected function createComponentTests(): TestsList
    {
        if ($this->getEvent()->isTeamEvent()) {
            return new TestsList($this->getContext(), DataTestFactory::getTeamTests($this->getContext()), false);
        } else {
            return new TestsList($this->getContext(), [], false); // @phpstan-ignore-line
        }
    }
}
