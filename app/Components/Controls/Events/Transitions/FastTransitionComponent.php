<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events\Transitions;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\Logging\Message;
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
        $this->template->transition = $this->getMachine()->getTransitionByStates($this->fromState, $this->toState);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'fast.latte');
    }

    /**
     * @throws BadTypeException
     */
    public function createComponentForm(): FormControl
    {
        $transition = $this->getMachine()->getTransitionByStates($this->fromState, $this->toState);
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $form->addText('id', _('Application Id'));
        $form->addSubmit('submit', $transition->label());
        $form->onSuccess[] = fn(Form $form) => $this->handleSave($form);
        return $control;
    }

    public function handleSave(Form $form): void
    {
        try {
            $values = $form->getValues('array');
            $machine = $this->getMachine();
            $transition = $this->getMachine()->getTransitionByStates($this->fromState, $this->toState);
            if ($this->event->isTeamEvent()) {
                $model = $this->event->getTeams()->where('fyziklani_team_id', $values['id'])->fetch();
            } else {
                $model = $this->event->getParticipants()->where('event_participant_id', $values['id'])->fetch();
            }
            if (!$model) {
                throw new NotFoundException();
            }
            $holder = $machine->createHolder($model);
            $machine->execute($transition, $holder);
            $this->getPresenter()->flashMessage(_('Transition successful'), Message::LVL_SUCCESS);
        } catch (NotFoundException $exception) {
            $this->getPresenter()->flashMessage(_('Application not found'), Message::LVL_ERROR);
        } catch (UnavailableTransitionsException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage(_('Error'), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }
}
