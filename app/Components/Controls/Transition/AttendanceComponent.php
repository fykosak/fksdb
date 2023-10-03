<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Components\MachineCode\FormComponent;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-template THolder of \FKSDB\Models\Transitions\Holder\ModelHolder
 */
class AttendanceComponent extends FormComponent
{
    /** @phpstan-var Machine<THolder> */
    protected Machine $machine;
    private EventModel $event;
    /** @var EnumColumn&FakeStringEnum */
    private FakeStringEnum $toState;

    /**
     * @param EnumColumn&FakeStringEnum $toState
     * @phpstan-param Machine<THolder> $machine
     */
    public function __construct(
        Container $container,
        EventModel $event,
        FakeStringEnum $toState,
        Machine $machine
    ) {
        parent::__construct($container);
        $this->toState = $toState;
        $this->event = $event;
        $this->machine = $machine;
    }

    final public function render(): void
    {
        $this->template->transition = $this->getTransition();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'attendance.latte';
    }

    /**
     * @phpstan-return Transition<THolder>
     */
    private function getTransition(): Transition
    {
        return $this->machine->getTransitionByTarget($this->toState);
    }

    protected function innerHandleSuccess(MachineCode $code, Form $form): void
    {
        try {
            if ($this->event->isTeamEvent() && $code->type === 'TE') {
                /** @var TeamModel2|null $model */
                $model = $this->event->getTeams()->where('fyziklani_team_id', $code->id)->fetch();
            } elseif ($code->type === 'EP') {
                /** @var EventParticipantModel|null $model */
                $model = $this->event->getParticipants()->where('event_participant_id', $code->id)->fetch();
            } else {
                throw new BadRequestException(_('Wrong type of code.'));
            }

            if (!$model) {
                throw new NotFoundException();
            }
            $holder = $this->machine->createHolder($model);
            $this->machine->execute($this->getTransition(), $holder);

            if ($this->event->isTeamEvent()) {
                /** @var TeamModel2|null $model */
                $this->getPresenter()->flashMessage(
                    sprintf(_('Transition successful for team: (%d) %s'), $model->fyziklani_team_id, $model->name),
                    Message::LVL_SUCCESS
                );
            } else {
                /** @var EventParticipantModel|null $model */
                $this->getPresenter()->flashMessage(
                    sprintf(_('Transition successful for application: %s'), $model->person->getFullName()),
                    Message::LVL_SUCCESS
                );
            }
        } catch (NotFoundException $exception) {
            $this->getPresenter()->flashMessage(_('Application not found'), Message::LVL_ERROR);
        } catch (UnavailableTransitionsException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage(_('Error'), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }

    protected function innerConfigureForm(Form $form): void
    {
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', $this->getTransition()->label());
    }
}
