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
     * @param mixed $compare greater|less
     */
    public function __construct($compare) {
        if ($compare == 'greater') {
            $this->sign = 1;
        } elseif ($compare == 'less') {
            $this->sign = -1;
        } else {
            throw new InvalidArgumentException;
        }
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
        if ($this->sign * $this->compare($trunk, $merged) > 0) {
            return $trunk;
        } else {
            return $merged;
        }
    }

    /**
     * @param $trunk
     * @param $merged
     * @return int|string
     */
    private function compare($trunk, $merged) {
        if ($trunk instanceof DateTime && $merged instanceof DateTime) {
            return $trunk->getTimestamp() - $merged->getTimestamp();
        } elseif (is_string($trunk) && is_string($merged)) {
            return strcmp($trunk, $merged);
        } elseif (is_numeric($trunk) && is_numeric($merged)) {
            return $trunk - $merged;
        } else {
            throw new CannotMergeException;
        }
    }

}
