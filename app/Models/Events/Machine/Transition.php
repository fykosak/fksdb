<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Machine;

use FKSDB\Models\Events\Exceptions\TransitionConditionFailedException;
use FKSDB\Models\Events\Exceptions\TransitionOnExecutedException;
use FKSDB\Models\Events\Exceptions\TransitionUnsatisfiedTargetException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use FKSDB\Models\Transitions\Transition\BehaviorType;

class Transition extends \FKSDB\Models\Transitions\Transition\Transition
{

    public BaseMachine $baseMachine;
    private string $mask;
    private string $name;
    private string $source;
    public string $target;
    private bool $visible;
    public array $onExecuted = [];

    public function __construct(string $mask, ?string $label = null, string $type = BehaviorType::DEFAULT)
    {
        $this->setMask($mask);
        $this->setLabel($label ?? '');
        $this->setBehaviorType($type);
    }

    /**
     * Meaningless identifier.
     */
    public function getName(): string
    {
        return $this->name;
    }

    private function setName(string $mask): void
    {
        // it's used for component naming
        $name = str_replace('*', '_any_', $mask);
        $name = str_replace('|', '_or_', $name);
        $this->name = preg_replace('/[^a-z0-9_]/i', '_', $name);
    }

    public function getMask(): string
    {
        return $this->mask;
    }

    public function setMask(string $mask): void
    {
        $this->mask = $mask;
        [$this->source, $target] = self::parseMask($mask);
        $this->setTargetState($target);
        $this->setName($mask);
    }

    public function setBaseMachine(BaseMachine $baseMachine): void
    {
        $this->baseMachine = $baseMachine;
    }

    public function setTargetState(string $target): void
    {
        $this->target = $target;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function isCreating(): bool
    {
        return strpos($this->source, AbstractMachine::STATE_INIT) !== false;
    }

    public function isTerminating(): bool
    {
        return $this->target === AbstractMachine::STATE_TERMINATED;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    private function validateTarget(BaseHolder $primaryHolder): ?array
    {
        $primaryHolder->validator->validate($primaryHolder);
        return $primaryHolder->validator->getValidationResult();
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
        if (!$this->isConditionFulfilled($holder)) {
            throw new TransitionConditionFailedException($this);
        }

        $this->changeState($holder);

        $validationResult = $this->validateTarget($holder);
        if (!is_null($validationResult)) {
            throw new TransitionUnsatisfiedTargetException($validationResult);
        }
    }

    /**
     * Triggers onExecuted event.
     *
     * @throws TransitionOnExecutedException
     */
    final public function executed(BaseHolder $holder): void
    {
        try {
            $this->callAfterExecute($holder, $this);
        } catch (\Throwable $exception) {
            throw new TransitionOnExecutedException($this->getName(), 0, $exception);
        }
    }

    /**
     * @note Assumes the condition is fulfilled.
     */
    private function changeState(BaseHolder $holder): void
    {
        $holder->setModelState($this->target);
    }

    /**
     * @param string $mask It may be either mask of initial state or mask of whole transition.
     */
    public function matches(string $mask): bool
    {
        $parts = self::parseMask($mask);

        if (count($parts) == 2 && $parts[1] != $this->target) {
            return false;
        }
        $stateMask = $parts[0];

        /*
         * Star matches any state but meta-states (initial and terminal)
         */
        if (
            strpos(AbstractMachine::STATE_ANY, $stateMask) !== false
            || (strpos(AbstractMachine::STATE_ANY, $this->source) !== false
                && ($mask != AbstractMachine::STATE_INIT
                    && $mask != AbstractMachine::STATE_TERMINATED))
        ) {
            return true;
        }

        return (bool)preg_match("/(^|\\|)$stateMask(\\||\$)/", $this->source);
    }

    /**
     * @note Assumes mask is valid.
     */
    private static function parseMask(string $mask): array
    {
        return explode('->', $mask);
    }

    public static function validateTransition(string $mask, array $states): bool
    {
        $parts = self::parseMask($mask);
        if (count($parts) != 2) {
            return false;
        }
        [$sources, $target] = $parts;

        $sources = explode('|', $sources);

        foreach ($sources as $source) {
            if (!in_array($source, array_merge($states, [AbstractMachine::STATE_ANY, AbstractMachine::STATE_INIT]))) {
                return false;
            }
        }
        if (!in_array($target, array_merge($states, [AbstractMachine::STATE_TERMINATED]))) {
            return false;
        }
        return true;
    }
}
