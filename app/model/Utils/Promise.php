<?php

namespace FKSDB\Utils;

use Nette\Object;

/**
 * Pseudopromise where we want to evaluate a value (provided as callback)
 * later than promise creation.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Promise extends Object {

    /**
     * @var callable
     */
    private $callback;
    /**
     * @var bool
     */
    private $called = false;
    /**
     * @var
     */
    private $value;

    /**
     * Promise constructor.
     * @param $callback
     */
    public function __construct(callable $callback) {
        $this->callback = $callback;
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
