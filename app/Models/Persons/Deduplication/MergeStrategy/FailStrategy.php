<?php

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FailStrategy implements MergeStrategy {

    /**
     * @param mixed $trunk
     * @param mixed $merged
     */
    public function mergeValues($trunk, $merged) {
        throw new CannotMergeException();
    }
}
