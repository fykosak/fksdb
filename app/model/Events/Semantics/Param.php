<?php

namespace Events\Semantics;

use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Parameter extends Object {

    use WithEventTrait;

    private $parameter;

    /**
     * Parameter constructor.
     * @param $parameter
     */
    function __construct($parameter) {
        $this->parameter = $parameter;
    }

    /**
     * @param $obj
     * @return mixed
     */
    public function __invoke($obj) {
        $holder = $this->getHolder($obj);
        return $holder->getParameter($this->parameter);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "param({$this->parameter})";
    }

}
