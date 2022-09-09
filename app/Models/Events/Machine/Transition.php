<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Machine;

use FKSDB\Models\Events\Exceptions\TransitionConditionFailedException;
use FKSDB\Models\Events\Exceptions\TransitionOnExecutedException;
use FKSDB\Models\Events\Exceptions\TransitionUnsatisfiedTargetException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\Transitions\Machine\AbstractMachine;

class Transition extends \FKSDB\Models\Transitions\Transition\Transition
{

    public BaseMachine $baseMachine;
    private string $id;
    private string $source;
    public EventParticipantStatus $target;
    private bool $visible;
    public array $onExecuted = [];

    public function __construct(string $mask, ?string $label)
    {
        $this->setMask($mask);
        $this->setLabel($label ?? '');
    }

    private function setName(string $mask): void
    {
        // it's used for component naming
        $name = str_replace('*', '_any_', $mask);
        $name = str_replace('|', '_or_', $name);
        $this->id = preg_replace('/[^a-z0-9_]/i', '_', $name);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setMask(string $mask): void
    {
        [$this->source, $target] = self::parseMask($mask);
        $this->setTargetStateEnum(EventParticipantStatus::tryFrom($target));
        $this->setName($mask);
    }

    public function setBaseMachine(BaseMachine $baseMachine): void
    {
        $this->baseMachine = $baseMachine;
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
        return $this->targetStateEnum->value === AbstractMachine::STATE_TERMINATED;
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
            throw new TransitionOnExecutedException($this->getId(), 0, $exception);
        }
    }

    /**
     * @note Assumes the condition is fulfilled.
     */
    private function changeState(BaseHolder $holder): void
    {
        $holder->setModelState($this->targetStateEnum);
    }

    public function matchSource(EnumColumn $source): bool
    {
        $stateMask = $source->value;
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
