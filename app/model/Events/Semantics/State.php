<?php

namespace Events\Semantics;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class State {
    use SmartObject;
    use WithEventTrait;

    private $state;

    /**
     * State constructor.
     * @param $state
     */
    function __construct($state) {
        $this->state = $state;
    }

    /**
     * @param $obj
     * @return bool
     */
    public function __invoke($obj) {
        $holder = $this->getHolder($obj);
        return $holder->getMachine()->getPrimaryMachine()->getState() == $this->state;
    }

    /**
     * @return string
     */
    public function __toString() {
        return "state == {$this->state}";
    }

}
