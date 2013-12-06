<?php

namespace Persons\Deduplication\MergeStrategy;

use Nette\Diagnostics\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class CummulativeStrategy implements IMergeStrategy {

    private $precedence;

    /**
     * 
     * @param null|enum $precedence trunk|merged
     */
    function __construct($precedence = null) {
        $this->precedence = $precedence;
    }

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
        } else if ($this->precedence == 'merged') {
            return $merged;
        }

        throw new CannotMergeException();
    }

    private function equals($trunk, $merged) {
        if ($trunk instanceof DateTime && $merged instanceof DateTime) {
            return $trunk->getTimestamp() == $merged->getTimestamp();
        } else {
            return $trunk == $merged;
        }
    }

}
