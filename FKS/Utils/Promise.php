<?php

namespace FKS\Utils;

use Nette\Callback;
use Nette\Object;

/**
 * Pseudopromise where we want to evaluate a value (provided as callback)
 * later than promise creation.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Promise extends Object {

    /**
     * @var Callback
     */
    private $callback;
    private $called = false;
    private $value;

    public function __construct($callback) {
        $this->callback = new Callback($callback);
    }

    public function getValue() {
        if (!$this->called) {
            $this->value = $this->callback->invoke();
            $this->called = true;
        }
        return $this->value;
    }

}
