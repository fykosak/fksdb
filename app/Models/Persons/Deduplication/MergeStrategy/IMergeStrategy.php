<?php

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IMergeStrategy {

    /**
     *
     * @param mixed $trunk
     * @param mixed $merged
     * @throws CannotMergeException
     */
    public function mergeValues($trunk, $merged);
}
