<?php

namespace Events\Payment;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Transition {

    /**
     * @var string
     */
    private $label;

    /**
     * @var boolean
     */
    private $dangerous;

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


    public function getLabel(): string {
        return $this->label;
    }

    public function isDangerous(): bool {
        return $this->dangerous;
    }

    public function setDangerous(bool $dangerous) {
        $this->dangerous = $dangerous;
    }

    public final function execute() {
        foreach ($this->onExecuted as $closure) {
            $closure();
        }
    }
}

