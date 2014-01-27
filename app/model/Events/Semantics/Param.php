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

    function __construct($parameter) {
        $this->parameter = $parameter;
    }

    public function __invoke($obj) {
        $holder = $this->getHolder($obj);
        return $holder->getParameter($this->parameter, null);
    }

}
