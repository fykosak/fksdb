<?php

namespace FKSDB\Events\Machine;

use FKSDB\Events\Model\Holder\Holder;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BaseMachine {

    public const STATE_INIT = '__init';
    public const STATE_TERMINATED = '__terminated';
    public const STATE_ANY = '*';
    public const EXECUTABLE = 0x1;
    public const VISIBLE = 0x2;

    private string $name;
    /**
     * @var string[]
     */
    private array $states;

    /**
     * @var Transition[]
     */
    private array $transitions = [];

    private Machine $machine;

    /**
     * BaseMachine constructor.
     * @param $name
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function addState(string $state, string $label): void {
        $this->states[$state] = $label;
    }

    /**
     * @return string[]
     */
    public function getStates(): array {
        return $this->states;
    }

    public function getMachine(): Machine {
        return $this->machine;
    }

    public function setMachine(Machine $machine): void {
        $this->machine = $machine;
    }

    public function addTransition(Transition $transition): void {
        $transition->setBaseMachine($this);
        $this->transitions[$transition->getName()] = $transition;
    }

    public function getTransition(string $name): Transition {
        return $this->transitions[$name];
    }

    public function addInducedTransition(string $transitionMask, array $induced): void {
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
    public function getStateName(string $state): string {
        switch ($state) {
            case self::STATE_INIT:
                return _('vznikající');
            case self::STATE_TERMINATED:
                return _('zaniklý');
            default:
                return _($state);
        }
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array {
        return $this->transitions;
    }

    /**
     * @param Holder $holder
     * @param string $sourceState
     * @param int $mode
     * @return Transition[]
     */
    public function getAvailableTransitions(Holder $holder, string $sourceState, $mode = self::EXECUTABLE): array {
        return array_filter($this->getMatchingTransitions($sourceState), function (Transition $transition) use ($mode, $holder) {
            return
                (!($mode & self::EXECUTABLE) || $transition->canExecute($holder)) && (!($mode & self::VISIBLE) || $transition->isVisible($holder));
        });
    }


    /**
     * @param string $sourceState
     * @param string $targetState
     * @return Transition[]|null
     */
    public function getTransitionByTarget(string $sourceState, string $targetState): ?array {
        $candidates = array_filter($this->getMatchingTransitions($sourceState), fn(Transition $transition) => $transition->getTarget() == $targetState);
        if (count($candidates) == 0) {
            return null;
        } elseif (count($candidates) > 1) {
            throw new InvalidArgumentException(sprintf('Target state %s is from state %s reachable via multiple edges.', $targetState, $sourceState)); //TODO may this be anytime useful?
        } else {
            return reset($candidates);
        }
    }

    /**
     * @param string $sourceStateMask
     * @return Transition[]
     */
    private function getMatchingTransitions(string $sourceStateMask): array {
        return array_filter($this->transitions, function (Transition $transition) use ($sourceStateMask) {
            return $transition->matches($sourceStateMask);
        });
    }
}
