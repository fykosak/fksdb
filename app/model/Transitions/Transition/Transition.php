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
    const TYPE_SUCCESS = ILogger::SUCCESS;
    const TYPE_WARNING = ILogger::WARNING;
    const TYPE_DANGER = ILogger::ERROR;
    const TYPE_PRIMARY = ILogger::PRIMARY;
    /**
     * @var Callable
     */
    private $condition;

    /**
     * @var string
     */
    private $type = self::TYPE_PRIMARY;
    /**
     * @var string
     */
    private $label;
    /**
     * @var callable[]
     */
    public $beforeExecuteCallbacks = [];
    /**
     * @var callable[]
     */
    public $afterExecuteCallbacks = [];

    /**
     * @var string
     */
    private $fromState;
    /**
     * @var string
     */
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

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type) {
        $this->type = $type;
    }

    public function getLabel(): string {
        return _($this->label);
    }

    /**
     * @param callable|Statement $callback
     */
    public function setCondition(callable $callback) {
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

    /**
     * @param IStateModel $model
     * @return void
     */
    final public function beforeExecute(IStateModel &$model) {
        foreach ($this->beforeExecuteCallbacks as $callback) {
            $callback($model);
        }
    }

    /**
     * @param IStateModel $model
     * @return void
     */
    final public function afterExecute(IStateModel &$model) {
        foreach ($this->afterExecuteCallbacks as $callback) {
            $callback($model);
        }
    }
}
