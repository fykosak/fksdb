<?php

namespace FKSDB\Models\Transitions\Transition;


use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\Transitions\Holder\IModelHolder;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use FKSDB\Models\Logging\ILogger;
use FKSDB\Models\Transitions\Machine\Machine;

/**
 * Class Transition
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Transition {

    use SmartObject;

    public const TYPE_SUCCESS = ILogger::SUCCESS;
    public const TYPE_WARNING = ILogger::WARNING;
    public const TYPE_DANGEROUS = ILogger::ERROR;
    public const TYPE_PRIMARY = ILogger::PRIMARY;
    public const TYPE_DEFAULT = 'secondary';

    protected const AVAILABLE_BEHAVIOR_TYPE = [
        self::TYPE_SUCCESS,
        self::TYPE_WARNING,
        self::TYPE_DANGEROUS,
        self::TYPE_DEFAULT,
        self::TYPE_PRIMARY,
    ];
    /** @var callable|bool */
    protected $condition;
    private string $behaviorType = self::TYPE_DEFAULT;
    private string $label;
    /** @var callable[] */
    public array $beforeExecuteCallbacks = [];
    /** @var callable[] */
    public array $afterExecuteCallbacks = [];
    protected string $sourceState;
    protected string $targetState;
    protected ExpressionEvaluator $evaluator;

    public function setSourceState(string $sourceState): void {
        $this->sourceState = $sourceState;
    }

    public function getSourceState(): string {
        return $this->sourceState;
    }

    public function matchSource(string $source): bool {
        return $this->getSourceState() === $source || $this->getSourceState() === Machine::STATE_ANY;
    }

    public function setTargetState(string $targetState): void {
        $this->targetState = $targetState;
    }

    public function getTargetState(): string {
        return $this->targetState;
    }

    public function isCreating(): bool {
        return $this->sourceState === Machine::STATE_INIT;
    }

    public function isTerminating(): bool {
        return $this->getTargetState() === Machine::STATE_TERMINATED;
    }

    public function getId(): string {
        return static::createId($this->sourceState, $this->targetState);
    }

    public static function createId(string $sourceState, string $targetState): string {
        return str_replace('*', '_any_', $sourceState) . '__' . $targetState;
    }

    public function getBehaviorType(): string {
        return $this->behaviorType;
    }

    public function setBehaviorType(string $behaviorType): void {
        if (!in_array($behaviorType, static::AVAILABLE_BEHAVIOR_TYPE)) {
            throw new InvalidArgumentException(sprintf('Behavior type %s not allowed', $behaviorType));
        }
        $this->behaviorType = $behaviorType;
    }

    protected function getEvaluator(): ExpressionEvaluator {
        return $this->evaluator;
    }

    public function setEvaluator(ExpressionEvaluator $evaluator): void {
        $this->evaluator = $evaluator;
    }

    public function getLabel(): string {
        return _($this->label);
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    /**
     * @param callable|bool $callback
     */
    public function setCondition($callback): void {
        $this->condition = $callback;
    }

    protected function isConditionFulfilled(...$args): bool {
        return (bool)$this->getEvaluator()->evaluate($this->condition, ...$args);
    }

    public function canExecute2(?IModelHolder $model): bool {
        return $this->isConditionFulfilled($model);
    }

    public function addBeforeExecute(callable $callBack): void {
        $this->beforeExecuteCallbacks[] = $callBack;
    }

    public function addAfterExecute(callable $callBack): void {
        $this->afterExecuteCallbacks[] = $callBack;
    }

    final public function callBeforeExecute(...$args): void {
        foreach ($this->beforeExecuteCallbacks as $callback) {
            $callback(...$args);
        }
    }

    final public function callAfterExecute(...$args): void {
        foreach ($this->afterExecuteCallbacks as $callback) {
            $callback(...$args);
        }
    }
}
