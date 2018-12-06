<?php

namespace FKSDB\EventPayment\Transition;

use Nette\Application\ForbiddenRequestException;

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
    public $onExecutedClosures = [];
    /**
     * @var \Closure[]
     */
    public $onExecuteClosures = [];

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

    function __construct(string $fromState = null, string $toState, string $label) {
        $this->fromState = $fromState;
        $this->toState = $toState;
        $this->label = $label;
    }

    public function getId() {
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

    /**
     * @param IStateModel $model
     * @return mixed
     */
    public function canExecute($model) {
        return ($this->condition)($model);
    }

    public final function onExecute(IStateModel $model) {
        if ($this->canExecute($model)) {
            foreach ($this->onExecuteClosures as $closure) {
                $closure($model);
            }
        } else {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
    }

    public final function onExecuted(IStateModel $model) {
        foreach ($this->onExecutedClosures as $closure) {
            $closure($model);
        }
    }
}

