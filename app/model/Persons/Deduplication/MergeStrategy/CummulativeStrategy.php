<?php

namespace Persons\Deduplication\MergeStrategy;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CummulativeStrategy implements IMergeStrategy {

    private $precedence;

    /**
     *
     * @param null|mixed $precedence trunk|merged
     */
    function __construct($precedence = null) {
        $this->precedence = $precedence;
    }

    /**
     * @param mixed $trunk
     * @param mixed $merged
     * @return mixed
     */
    public function mergeValues($trunk, $merged) {
        if ($merged === null) {
            return $trunk;
        }
        if ($trunk === null) {
            return $merged;
        }
        if ($this->equals($trunk, $merged)) {
            return $trunk;
        }

        if ($this->precedence == 'trunk') {
            return $trunk;
        } elseif ($this->precedence == 'merged') {
            return $merged;
        }

        throw new CannotMergeException;
    }

    /**
     * @param $trunk
     * @param $merged
     * @return bool
     */
    private function equals($trunk, $merged) {
        if ($trunk instanceof \DateTime && $merged instanceof \DateTime) {
            return $trunk->getTimestamp() == $merged->getTimestamp();
        } else {
            return $trunk == $merged;
        }
    }

}
