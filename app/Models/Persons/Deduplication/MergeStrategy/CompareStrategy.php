<?php

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CompareStrategy implements MergeStrategy {

    private int $sign;

    public function __construct(string $compare) {
        if ($compare == 'greater') {
            $this->sign = 1;
        } elseif ($compare == 'less') {
            $this->sign = -1;
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @param mixed $trunk
     * @param mixed $merged
     * @return mixed
     */
    public function mergeValues($trunk, $merged) {
        if (is_null($merged)) {
            return $trunk;
        }
        if (is_null($trunk)) {
            return $merged;
        }
        if ($this->sign * $this->compare($trunk, $merged) > 0) {
            return $trunk;
        } else {
            return $merged;
        }
    }

    /**
     * @param mixed $trunk
     * @param mixed $merged
     * @return int|string
     */
    private function compare($trunk, $merged): int {
        if ($trunk instanceof \DateTime && $merged instanceof \DateTime) {
            return $trunk->getTimestamp() - $merged->getTimestamp();
        } elseif (is_string($trunk) && is_string($merged)) {
            return strcmp($trunk, $merged);
        } elseif (is_numeric($trunk) && is_numeric($merged)) {
            return $trunk - $merged;
        } else {
            throw new CannotMergeException();
        }
    }

}
