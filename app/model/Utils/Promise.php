<?php

namespace FKSDB\Utils;

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

    /**
     * Promise constructor.
     * @param $callback
     */
    public function __construct($callback) {
        $this->callback = new Callback($callback);
    }

    /**
     * @return mixed
     */
    public function getValue() {
        if (!$this->called) {
            $this->value = $this->callback->invoke();
            $this->called = true;
        }
        return $this->value;
    }

}
