<?php

namespace FKSDB\Transitions;

use FKSDB\Logging\ILogger;
use FKSDB\Transitions\Statements\Statement;
use Tracy\Debugger;

/**
 * Class Transition
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class Transition {
    const TYPE_SUCCESS = ILogger::SUCCESS;
    const TYPE_WARNING = ILogger::WARNING;
    const TYPE_DANGER = ILogger::ERROR;
    const TYPE_PRIMARY = ILogger::PRIMARY;
    /** @var Callable */
    private $condition;

    /** @var string */
    private $type = self::TYPE_PRIMARY;
    /** @var string */
    private $label;
    /** @var callable[] */
    public $beforeExecuteCallbacks = [];
    /** @var callable[] */
    public $afterExecuteCallbacks = [];

    /** @var string */
    private $fromState;
    /** @var string */
    private $toState;

    /**
     * @return string
     */
    public function getFromState() {
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

    public function canExecute(?IStateModel $model): bool {
        Debugger::barDump($this->condition);
        return ($this->condition)($model);
    }

    public function addBeforeExecute(callable $callBack): void {
        $this->beforeExecuteCallbacks[] = $callBack;
    }

    public function addAfterExecute(callable $callBack): void {
        $this->afterExecuteCallbacks[] = $callBack;
    }

    final public function beforeExecute(IStateModel $model): void {
        foreach ($this->beforeExecuteCallbacks as $callback) {
            $callback($model);
        }
    }

    final public function afterExecute(IStateModel $model): void {
        foreach ($this->afterExecuteCallbacks as $callback) {
            $callback($model);
        }
    }
}
