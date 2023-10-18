<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\Code;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-template THolder of \FKSDB\Models\Transitions\Holder\ModelHolder
 * @phpstan-type TActions 'edit'|'transition'|'detail'
 */
final class CodeComponent extends FormComponent
{
    /** @phpstan-var Machine<THolder> */
    protected Machine $machine;
    private EventModel $event;
    /** @var EnumColumn&FakeStringEnum */
    private FakeStringEnum $toState;
    /**
     * @persistent
     * @phpstan-var TActions
     */
    public ?string $action = null;

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
        $this->template->transitions = $this->getTransitions();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte';
    }

    /**
     * @phpstan-return Transition<THolder>[]
     */
    private function getTransitions(): array
    {
        return $this->machine->getTransitionsByTarget($this->toState);
    }

    /**
     * @phpstan-param EventParticipantModel|TeamModel2 $model
     * @throws \Throwable
     */
    private function innerHandleTransition(Model $model): void
    {
        $holder = $this->machine->createHolder($model);
        $transitions = $this->machine->getAvailableTransitions($holder);
        $executed = false;
        foreach ($transitions as $transition) {
            if ($transition->target === $this->toState) {
                $this->machine->execute($transition, $holder);
                $executed = true;
            }
        }
        if (!$executed) {
            throw new UnavailableTransitionsException();
        }

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
    }

    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{code:string,action:TActions} $values */
        $values = $form->getValues('array');
        $this->action = $values['action'];
        try {
            $application = $this->resolveApplication(
                MachineCode::parseHash(
                    $this->container,
                    $values['code'],
                    MachineCode::getSaltForEvent($this->event)
                )
            );
            if ($application->event_id !== $this->event->event_id) {
                throw new BadRequestException(_('Application belongs to another event.'));
            }
            switch ($values['action']) {
                case 'edit':
                    $this->getPresenter()->redirect('edit', ['id' => $application->getPrimary()]);
                    break;// @phpstan-ignore-line
                case 'detail':
                    $this->getPresenter()->redirect('detail', ['id' => $application->getPrimary()]);
                    break;// @phpstan-ignore-line
                case 'transition':
                    $this->innerHandleTransition($application);
            }
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage(_('Error: ') . $exception->getMessage(), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }

    /**
     * @return TeamModel2|EventParticipantModel
     * @throws BadRequestException
     * @throws NotFoundException
     */
    private function resolveApplication(Model $model): Model
    {
        if ($model instanceof EventParticipantModel) {
            return $model;
        } elseif ($model instanceof TeamModel2) {
            return $model;
        } elseif ($model instanceof PersonModel) {
            return $model->getApplication($this->event);
        }
        throw new BadRequestException(_('Wrong type of code.'));
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'));
        $form->addSelect(
            'action',
            _('Action'),
            [
                'edit' => _('button.edit'),
                'transition' => _('Transition!'),
                'detail' => _('button.detail'),
            ]
        )->setDefaultValue($this->action);
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Do!'));
    }
}
