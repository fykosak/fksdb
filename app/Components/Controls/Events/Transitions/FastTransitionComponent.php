<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events\Transitions;

use FKSDB\Components\Controls\Events\AttendanceCode;
use FKSDB\Components\Controls\FormControl\FormControl;
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
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

class FastTransitionComponent extends TransitionComponent
{
    /** @var EnumColumn&FakeStringEnum */
    private FakeStringEnum $fromState;
    /** @var EnumColumn&FakeStringEnum */
    private FakeStringEnum $toState;

    public function __construct(
        Container $container,
        EventModel $event,
        FakeStringEnum $fromState,
        FakeStringEnum $toState
    ) {
        parent::__construct($container, $event);
        $this->fromState = $fromState;
        $this->toState = $toState;
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    final public function render(): void
    {
        $this->template->transition = $this->getTransition();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'fast.latte');
    }

    /**
     * @throws BadTypeException
     */
    public function createComponentForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $form->addText('code', _('Application Id'));
        $form->addCheckbox('bypass', _('Bypass checksum'));
        $form->addSubmit('submit', $this->getTransition()->label());
        $form->onSuccess[] = fn(Form $form) => $this->handleSave($form);
        return $control;
    }

    public function handleSave(Form $form): void
    {
        try {
            $values = $form->getValues('array');
            if ($values['bypass']) {
                $id = +$values['code'];
            } else {
                $id = AttendanceCode::checkCode($this->container, $values['code']);
            }
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
                    sprintf(_('Transition successful team: (%d) %s'), $model->fyziklani_team_id, $model->name),
                    Message::LVL_SUCCESS
                );
            } else {
                $this->getPresenter()->flashMessage(
                    sprintf(_('Transition successful application: %s'), $model->person->getFullName()),
                    Message::LVL_SUCCESS
                );
            }
        } catch (NotFoundException $exception) {
            $this->getPresenter()->flashMessage(_('Application not found'), Message::LVL_ERROR);
        } catch (UnavailableTransitionsException | ForbiddenRequestException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage(_('Error'), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }

    /**
     * @throws BadTypeException
     */
    private function getTransition(): Transition
    {
        return $this->getMachine()->getTransitionByStates($this->fromState, $this->toState);
    }
}
