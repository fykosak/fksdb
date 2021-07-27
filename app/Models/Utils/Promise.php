<?php

namespace FKSDB\Models\Utils;

use Nette\SmartObject;

/**
 * Pseudopromise where we want to evaluate a value (provided as callback)
 * later than promise creation.
 */
class Promise {
    use SmartObject;

    /** @var callable */
    private $callback;

    private bool $called = false;
    /** @var mixed */
    private $value;

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        if (!$this->called) {
            $this->value = ($this->callback)();
            $this->called = true;
        }
        return $this->value;
    }
}
