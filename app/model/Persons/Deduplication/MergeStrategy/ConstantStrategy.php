<?php

namespace Persons\Deduplication\MergeStrategy;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ConstantStrategy implements IMergeStrategy {

    private $constant;

    /**
     * ConstantStrategy constructor.
     * @param $constant
     */
    public function __construct($constant) {
        $this->constant = $constant;
    }

    /**
     * @param $trunk
     * @param $merged
     * @return mixed
     */
    public function mergeValues($trunk, $merged) {
        return $this->constant;
    }

}
