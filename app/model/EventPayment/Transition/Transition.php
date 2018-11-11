<?php

namespace FKSDB\EventPayment\Transition;

use FKSDB\ORM\ModelEventPayment;

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

    private $type = self::TYPE_PRIMARY;
    /**
     * @var string
     */
    private $label;

    /**
     * @var boolean
     */
    private $dangerous = false;

    /**
     * @var \Closure[]
     */
    public $onExecuted = [];

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

    public function isDangerous(): bool {
        return $this->dangerous;
    }

    public function setDangerous(bool $dangerous) {
        $this->dangerous = $dangerous;
    }

    public final function execute(ModelEventPayment $model) {
        foreach ($this->onExecuted as $closure) {
            $closure($model);
        }
    }
}

