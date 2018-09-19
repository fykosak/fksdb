<?php

namespace Persons\Deduplication\MergeStrategy;

use DateTime;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CompareStrategy implements IMergeStrategy {

    private $sign;

    /**
     *
     * @param enum $compare greater|less
     */
    function __construct($compare) {
        if ($compare == 'greater') {
            $this->sign = 1;
        } else if ($compare == 'less') {
            $this->sign = -1;
        } else {
            throw new InvalidArgumentException();
        }
    }

    public function mergeValues($trunk, $merged) {
        if ($merged === null) {
            return $trunk;
        }
        if ($trunk === null) {
            return $merged;
        }
        if ($this->sign * $this->compare($trunk, $merged) > 0) {
            return $trunk;
        } else {
            return $merged;
        }
    }

    private function compare($trunk, $merged) {
        if ($trunk instanceof DateTime && $merged instanceof DateTime) {
            return $trunk->getTimestamp() - $merged->getTimestamp();
        } else if (is_string($trunk) && is_string($merged)) {
            return strcmp($trunk, $merged);
        } else if (is_numeric($trunk) && is_numeric($merged)) {
            return $trunk - $merged;
        } else {
            throw new CannotMergeException();
        }
    }

}
