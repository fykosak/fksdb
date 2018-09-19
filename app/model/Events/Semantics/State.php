<?php

namespace Events\Semantics;

use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class State extends Object {

    use WithEventTrait;

    private $state;

    function __construct($state) {
        $this->state = $state;
    }

    public function __invoke($obj) {
        $holder = $this->getHolder($obj);
        return $holder->getMachine()->getPrimaryMachine()->getState() == $this->state;
    }

    public function __toString() {
        return "state == {$this->state}";
    }

}
