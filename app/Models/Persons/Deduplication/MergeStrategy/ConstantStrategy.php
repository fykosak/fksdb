<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

class ConstantStrategy implements MergeStrategy {

    /** @var mixed */
    private $constant;

    /**
     * ConstantStrategy constructor.
     * @param mixed $constant
     */
    public function __construct($constant) {
        $this->constant = $constant;
    }

    /**
     * @param mixed $trunk
     * @param mixed $merged
     * @return mixed
     */
    public function mergeValues($trunk, $merged) {
        return $this->constant;
    }

}
