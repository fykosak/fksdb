<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\CodeTransition;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * @phpstan-template THolder of \FKSDB\Models\Transitions\Holder\ParticipantHolder|\FKSDB\Models\Transitions\Holder\TeamHolder
 */
class CodeTransitionComponent extends CodeForm
{
    /** @phpstan-var Machine<THolder> */
    protected Machine $machine;
    /** @var TeamModel2|EventParticipantModel */
    private Model $model;

    /** @var TeamState|EventParticipantStatus */
    private FakeStringEnum $targetState;

    /**
     * @param TeamState|EventParticipantStatus $targetState
     * @phpstan-param Machine<THolder> $machine
     * @param TeamModel2|EventParticipantModel $model
     */
    public function __construct(
        Container $container,
        Model $model,
        FakeStringEnum $targetState,
        Machine $machine
    ) {
        parent::__construct($container);
        $this->targetState = $targetState;
        $this->model = $model;
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
        return $this->machine->getTransitionsByTarget($this->targetState);
    }

    /**
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws \Throwable
     */
    protected function innerHandleSuccess(Model $model): void
    {
        $application = $this->resolveApplication($model);
        if ($model->getPrimary() !== $this->model->getPrimary()) {
            throw new BadRequestException(_('Modely sa nezhodujú'));
        }
        $holder = $this->machine->createHolder($application);
        $transitions = $this->machine->getAvailableTransitions($holder);
        $executed = false;
        foreach ($transitions as $transition) {
            if ($transition->target->value === $this->targetState->value) {
                $this->machine->execute($transition, $holder);
                $executed = true;
            }
        }
        if (!$executed) {
            throw new UnavailableTransitionsException();
        }

        $this->getPresenter()->flashMessage(
            $application instanceof TeamModel2
                ? sprintf(_('Transition successful for: %s'), $application->name)
                : sprintf(_('Transition successful for: %s'), $application->person->getFullName()),
            Message::LVL_SUCCESS
        );
    }

    /**
     * @return TeamModel2|EventParticipantModel
     * @throws BadRequestException
     * @throws NotFoundException
     */
    private function resolveApplication(Model $model): Model
    {
        if ($model instanceof PersonModel) {
            return $model->getApplication($this->model->event);
        } elseif ($model instanceof EventParticipantModel || $model instanceof TeamModel2) {
            return $model;
        } else {
            throw new BadRequestException(_('Wrong type of code.'));
        }
    }

    /**
     * @throws NotImplementedException
     */
    protected function getSalt(): string
    {
        return MachineCode::getSaltForEvent($this->model->event);
    }
}
