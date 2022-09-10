<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Machine;

use FKSDB\Models\Events\Exceptions\TransitionConditionFailedException;
use FKSDB\Models\Events\Exceptions\TransitionUnsatisfiedTargetException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;

class Transition extends \FKSDB\Models\Transitions\Transition\Transition
{

    public BaseMachine $baseMachine;
    private bool $visible;

    public function setBaseMachine(BaseMachine $baseMachine): void
    {
        $this->baseMachine = $baseMachine;
    }

    public function isCreating(): bool
    {
        return $this->source->value === AbstractMachine::STATE_INIT;
    }

    public function isTerminating(): bool
    {
        return $this->target->value === AbstractMachine::STATE_TERMINATED;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    private function validateTarget(BaseHolder $holder): ?array
    {
        return $holder->validator->validate($holder);
    }

    /**
     * @return bool|callable
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Launch induced transitions and sets new state.
     * @throws TransitionConditionFailedException
     * @throws TransitionUnsatisfiedTargetException
     * @todo Induction work only for one level.
     */
    final public function execute(BaseHolder $holder): void
    {
        if (!$this->canExecute($holder)) {
            throw new TransitionConditionFailedException($this);
        }

        $this->changeState($holder);

        $validationResult = $this->validateTarget($holder);
        if (!is_null($validationResult)) {
            throw new TransitionUnsatisfiedTargetException($validationResult);
        }
    }

    /**
     * @note Assumes the condition is fulfilled.
     */
    private function changeState(BaseHolder $holder): void
    {
        $holder->setModelState($this->target);
    }
}
