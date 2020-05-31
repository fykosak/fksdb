<?php

namespace FKSDB\Transitions;

use FKSDB\Logging\ILogger;
use FKSDB\Transitions\Statements\Statement;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
final class Transition {
    public const TYPE_SUCCESS = ILogger::SUCCESS;
    public const TYPE_WARNING = ILogger::WARNING;
    public const TYPE_DANGER = ILogger::ERROR;
    public const TYPE_PRIMARY = ILogger::PRIMARY;
    /**
     * @var Callable
     */
    private $condition;

    private string $type = self::TYPE_PRIMARY;

    private string $label;
    /**
     * @var callable[]
     */
    public $beforeExecuteCallbacks = [];
    /**
     * @var callable[]
     */
    public $afterExecuteCallbacks = [];

    private string $fromState;

    private string $toState;

    public function getFromState(): string {
        return $this->fromState;
    }

    public function getToState(): string {
        return $this->toState;
    }

    /**
     * Transition constructor.
     * @param string $fromState
     * @param string $toState
     * @param string $label
     */
    public function __construct(string $fromState, string $toState, string $label) {
        $this->fromState = $fromState;
        $this->toState = $toState;
        $this->label = $label;
    }

    public function getId(): string {
        return $this->fromState . '__' . $this->toState;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getLabel(): string {
        return _($this->label);
    }

    /**
     * @param callable|Statement $callback
     */
    public function setCondition(callable $callback): void {
        $this->condition = $callback;
    }

    public function isCreating(): bool {
        return $this->fromState === Machine::STATE_INIT;
    }

    /**
     * @param IStateModel $model
     * @return bool
     */
    public function canExecute($model): bool {
        return ($this->condition)($model);
    }

    final public function beforeExecute(IStateModel &$model): void {
        foreach ($this->beforeExecuteCallbacks as $callback) {
            $callback($model);
        }
    }

    final public function afterExecute(IStateModel &$model): void {
        foreach ($this->afterExecuteCallbacks as $callback) {
            $callback($model);
        }
    }
}
