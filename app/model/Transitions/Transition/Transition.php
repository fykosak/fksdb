<?php

namespace FKSDB\Transitions;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Transition {
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_DANGER = 'danger';
    const TYPE_PRIMARY = 'primary';
    /**
     * @var  \Closure
     */
    private $condition;

    private $type = self::TYPE_PRIMARY;
    /**
     * @var string
     */
    private $label;
    /**
     * @var \Closure[]
     */
    public $beforeExecuteClosures = [];
    /**
     * @var \Closure[]
     */
    public $afterExecuteClosures = [];

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

    /**
     * @return string
     */
    public function getToState(): string {
        return $this->toState;
    }

    function __construct(string $fromState, string $toState, string $label) {
        $this->fromState = $fromState;
        $this->toState = $toState;
        $this->label = $label;
    }

    public function getId(): string {
        return $this->fromState . '__' . $this->toState;
    }

    public function getType() {
        return $this->type;
    }

    public function setType(string $type) {
        $this->type = $type;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function setCondition(\Closure $closure) {
        $this->condition = $closure;
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
     */
    public final function beforeExecute(IStateModel &$model) {
        foreach ($this->beforeExecuteClosures as $closure) {
            $closure($model);
        }
    }

    /**
     * @param IStateModel $model
     */
    public final function afterExecute(IStateModel &$model) {
        foreach ($this->afterExecuteClosures as $closure) {
            $closure($model);
        }
    }
}

