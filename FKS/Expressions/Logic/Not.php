<?php

namespace FKS\Expressions\Logic;

use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Not extends Object {

    private $expression;

    function __construct($expression) {
        $this->expression = $expression;
    }

    public function __invoke() {
        $args = func_get_args();
        if (is_callable($this->expression)) {
            return !call_user_func_array($this->expression, $args);
        } else {
            return !$this->expression;
        };
    }

}
