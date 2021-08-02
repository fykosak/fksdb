<?php

namespace FKSDB\Models\Events\Machine;

use FKSDB\Models\Events\Model\Holder\Holder;
use Nette\InvalidArgumentException;

class BaseMachine
{

    private string $name;
    private array $states;
    private array $transitions = [];
    private Machine $machine;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addState(string $state): void
    {
        $this->states[] = $state;
    }

    public function getStates(): array
    {
        return $this->states;
    }

    public function getMachine(): Machine
    {
        return $this->machine;
    }

    public function setMachine(Machine $machine): void
    {
        $this->machine = $machine;
    }

    public function addTransition(Transition $transition): void
    {
        $transition->setBaseMachine($this);
        $this->transitions[$transition->getName()] = $transition;
    }

    public function getTransition(string $name): Transition
    {
        return $this->transitions[$name];
    }

    public function addInducedTransition(string $transitionMask, array $induced): void
    {
        foreach ($this->getMatchingTransitions($transitionMask) as $transition) {
            foreach ($induced as $machineName => $state) {
                $targetMachine = $this->getMachine()->getBaseMachine($machineName);
                $transition->addInducedTransition($targetMachine, $state);
            }
        }
    }

    /**
     * @param string state identification
     * @return string
     */
    public function getStateName(string $state): string
    {
        switch ($state) {
            case \FKSDB\Models\Transitions\Machine\Machine::STATE_INIT:
                return _('initial');
            case \FKSDB\Models\Transitions\Machine\Machine::STATE_TERMINATED:
                return _('terminated');
            default:
                return _($state);
        }
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    /**
     * @param Holder $holder
     * @param string $sourceState
     * @param bool $visible
     * @param bool $executable
     * @return Transition[]
     */
    public function getAvailableTransitions(Holder $holder, string $sourceState, bool $visible = false, bool $executable = true): array
    {
        return array_filter($this->getMatchingTransitions($sourceState), function (Transition $transition) use ($holder, $executable, $visible): bool {
            return
                (!$executable || $transition->canExecute($holder)) && (!$visible || $transition->isVisible($holder));
        });
    }

    public function getTransitionByTarget(string $sourceState, string $targetState): ?Transition
    {
        $candidates = array_filter($this->getMatchingTransitions($sourceState), function (Transition $transition) use ($targetState): bool {
            return $transition->getTargetState() == $targetState;
        });
        if (count($candidates) == 0) {
            return null;
        } elseif (count($candidates) > 1) {
            throw new InvalidArgumentException(sprintf('Target state %s is from state %s reachable via multiple edges.', $targetState, $sourceState));
        } else {
            return reset($candidates);
        }
    }

    /**
     * @param string $sourceStateMask
     * @return Transition[]
     */
    private function getMatchingTransitions(string $sourceStateMask): array
    {
        return array_filter($this->transitions, function (Transition $transition) use ($sourceStateMask): bool {
            return $transition->matches($sourceStateMask);
        });
    }
}
