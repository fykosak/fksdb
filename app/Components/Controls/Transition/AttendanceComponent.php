<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Components\CodeProcessing\CodeFormComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

class AttendanceComponent extends CodeFormComponent
{
    use TransitionComponent;

    /** @var EnumColumn&FakeStringEnum */
    private FakeStringEnum $fromState;
    /** @var EnumColumn&FakeStringEnum */
    private FakeStringEnum $toState;

    /**
     * @param EnumColumn&FakeStringEnum $fromState
     * @param EnumColumn&FakeStringEnum $toState
     */
    public function __construct(
        Container $container,
        EventModel $event,
        FakeStringEnum $fromState,
        FakeStringEnum $toState
    ) {
        parent::__construct($container);
        $this->fromState = $fromState;
        $this->toState = $toState;
        $this->event = $event;
    }

    /**
     * @phpstan-param array<string,mixed> $params
     * @throws BadTypeException
     */
    final public function render(array $params = []): void
    {
        parent::render(['transition' => $this->getTransition()]);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'attendance.latte';
    }

    /**
     * @throws BadTypeException
     */
    private function getTransition(): Transition
    {
        return $this->getMachine()->getTransitionByStates($this->fromState, $this->toState);
    }

    protected function innerHandleSuccess(string $id, Form $form): void
    {
        try {
            $machine = $this->getMachine();
            if ($this->event->isTeamEvent()) {
                $model = $this->event->getTeams()->where('fyziklani_team_id', $id)->fetch();
            } else {
                $model = $this->event->getParticipants()->where('event_participant_id', $id)->fetch();
            }
            /** @var TeamModel2|EventParticipantModel|null $model */
            if (!$model) {
                throw new NotFoundException();
            }
            $holder = $machine->createHolder($model);
            $machine->execute($this->getTransition(), $holder);
            if ($this->event->isTeamEvent()) {
                $this->getPresenter()->flashMessage(
                    sprintf(_('Transition successful for team: (%d) %s'), $model->fyziklani_team_id, $model->name),
                    Message::LVL_SUCCESS
                );
            } else {
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

    /**
     * @throws BadTypeException
     */
    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', $this->getTransition()->label());
    }
}
