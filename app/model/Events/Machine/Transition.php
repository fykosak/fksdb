<?php

namespace FKSDB\Events\Machine;

use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\TransitionConditionFailedException;
use FKSDB\Events\TransitionOnExecutedException;
use FKSDB\Events\TransitionUnsatisfiedTargetException;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Transition extends \FKSDB\Transitions\Transition {

    private BaseMachine $baseMachine;

    private array $inducedTransitions = [];

    private string $mask;

    private string $name;

    private string $source;

    /** @var bool|callable */
    private $visible;

    /**
     * Transition constructor.
     * @param string $mask
     * @param string $label
     * @param string $type
     */
    public function __construct(string $mask, ?string $label = null, string $type = self::TYPE_DEFAULT) {
        $this->setMask($mask);
        $this->setLabel($label ?? '');
        $this->setBehaviorType($type);
    }

    /**
     * Meaningless idenifier.
     */
    public function getName(): string {
        return $this->name;
    }

    public function getBehaviorType(): string {
        if ($this->isTerminating()) {
            return self::TYPE_DANGEROUS;
        }
        if ($this->isCreating()) {
            return self::TYPE_SUCCESS;
        }
        return parent::getBehaviorType();
    }

    private function setName(string $mask): void {
        // it's used for component naming
        $name = str_replace('*', '_any_', $mask);
        $name = str_replace('|', '_or_', $name);
        $this->name = preg_replace('/[^a-z0-9_]/i', '_', $name);
    }

    public function getMask(): string {
        return $this->mask;
    }

    public function setMask(string $mask): void {
        $this->mask = $mask;
        [$this->source, $target] = self::parseMask($mask);
        $this->setTargetState($target);
        $this->setName($mask);
    }

    public function getBaseMachine(): BaseMachine {
        return $this->baseMachine;
    }

    public function setBaseMachine(BaseMachine $baseMachine): void {
        $this->baseMachine = $baseMachine;
    }

    public function getSource(): string {
        return $this->source;
    }

    public function isCreating(): bool {
        return strpos($this->source, \FKSDB\Transitions\Machine::STATE_INIT) !== false;
    }

    public function isVisible(Holder $holder): bool {
        return $this->getEvaluator()->evaluate($this->visible, $holder);
    }

    /**
     * @param callable|bool $visible
     * @return void
     */
    public function setVisible($visible): void {
        $this->visible = $visible;
    }

    public function addInducedTransition(BaseMachine $targetMachine, string $targetState): void {
        if ($targetMachine === $this->getBaseMachine()) {
            throw new InvalidArgumentException("Cannot induce transition in the same machine.");
        }
        $targetName = $targetMachine->getName();
        if (isset($this->inducedTransitions[$targetName])) {
            throw new InvalidArgumentException("Induced transition for machine $targetName already defined in " . $this->getName() . ".");
        }
        $this->inducedTransitions[$targetName] = $targetState;
    }

    /**
     * @param Holder $holder
     * @return Transition[]
     */
    private function getInducedTransitions(Holder $holder): array {
        $result = [];
        foreach ($this->inducedTransitions as $baseMachineName => $targetState) {
            $targetMachine = $this->getBaseMachine()->getMachine()->getBaseMachine($baseMachineName);
            $oldState = $holder->getBaseHolder($baseMachineName)->getModelState();
            $inducedTransition = $targetMachine->getTransitionByTarget($oldState, $targetState);
            if ($inducedTransition) {
                $result[$baseMachineName] = $inducedTransition;
            }
        }
        return $result;
    }

    private function getBlockingTransition(Holder $holder): ?Transition {
        foreach ($this->getInducedTransitions($holder) as $inducedTransition) {
            if ($inducedTransition->getBlockingTransition($holder)) {
                return $inducedTransition;
            }
        }
        if (!$this->isConditionFulfilled($holder)) {
            return $this;
        }
        return null;
    }

    /**
     * @param Holder $holder
     * @param Transition[] $inducedTransitions
     * @return bool
     */
    private function validateTarget(Holder $holder, array $inducedTransitions): bool {
        foreach ($inducedTransitions as $inducedTransition) {
            if (($result = $inducedTransition->validateTarget($holder, [])) !== true) { // intentionally =
                return $result;
            }
        }

        $baseHolder = $holder->getBaseHolder($this->getBaseMachine()->getName());
        $validator = $baseHolder->getValidator();
        $validator->validate($baseHolder);
        return $validator->getValidationResult();
    }

    final public function canExecute(Holder $holder): bool {
        return !$this->getBlockingTransition($holder);
    }

    /**
     * @return bool|callable
     */
    public function getCondition() {
        return $this->condition;
    }

    /**
     * Launch induced transitions and sets new state.
     *
     * @param Holder $holder
     * @return array
     * @todo Induction work only for one level.
     */
    final public function execute(Holder $holder): array {
        $blockingTransition = $this->getBlockingTransition($holder);
        if ($blockingTransition) {
            throw new TransitionConditionFailedException($blockingTransition);
        }

        $inducedTransitions = [];
        foreach ($this->getInducedTransitions($holder) as $holderName => $inducedTransition) {
            $inducedTransition->changeState($holder->getBaseHolder($holderName));
            $inducedTransitions[] = $inducedTransition;
        }

        $this->changeState($holder->getBaseHolder($this->getBaseMachine()->getName()));

        $validationResult = $this->validateTarget($holder, $inducedTransitions);
        if ($validationResult !== true) {
            throw new TransitionUnsatisfiedTargetException($validationResult);
        }

        return $inducedTransitions;
    }

    /**
     * Triggers onExecuted event.
     *
     * @param Holder $holder
     * @param Transition[] $inducedTransitions
     */
    final public function executed(Holder $holder, array $inducedTransitions): void {
        foreach ($inducedTransitions as $inducedTransition) {
            $inducedTransition->executed($holder, []);
        }
        try {
            $this->callAfterExecute($this,$holder);
        } catch (\Exception $exception) {
            throw new TransitionOnExecutedException($this->getName(), null, $exception);
        }
    }

    /**
     * @note Assumes the condition is fullfilled.
     * @param BaseHolder $holder
     */
    private function changeState(BaseHolder $holder): void {
        $holder->setModelState($this->getTargetState());
    }

    /**
     * @param string $mask It may be either mask of initial state or mask of whole transition.
     * @return bool
     */
    public function matches(string $mask): bool {
        $parts = self::parseMask($mask);

        if (count($parts) == 2 && $parts[1] != $this->getTargetState()) {
            return false;
        }
        $stateMask = $parts[0];

        /*
         * Star matches any state but meta-states (initial and terminal)
         */
        if (strpos(\FKSDB\Transitions\Machine::STATE_ANY, $stateMask) !== false || (strpos(\FKSDB\Transitions\Machine::STATE_ANY, $this->source) !== false &&
                ($mask != \FKSDB\Transitions\Machine::STATE_INIT && $mask != \FKSDB\Transitions\Machine::STATE_TERMINATED))) {
            return true;
        }

        return preg_match("/(^|\\|){$stateMask}(\\||\$)/", $this->source);
    }

    /**
     * @note Assumes mask is valid.
     *
     * @param string $mask
     * @return array
     */
    private static function parseMask(string $mask): array {
        return explode('->', $mask);
    }

    public static function validateTransition(string $mask, array $states): bool {
        $parts = self::parseMask($mask);
        if (count($parts) != 2) {
            return false;
        }
        [$sources, $target] = $parts;

        $sources = explode('|', $sources);

        foreach ($sources as $source) {
            if (!in_array($source, array_merge($states, [\FKSDB\Transitions\Machine::STATE_ANY, \FKSDB\Transitions\Machine::STATE_INIT]))) {
                return false;
            }
        }
        if (!in_array($target, array_merge($states, [\FKSDB\Transitions\Machine::STATE_TERMINATED]))) {
            return false;
        }
        return true;
    }
}
