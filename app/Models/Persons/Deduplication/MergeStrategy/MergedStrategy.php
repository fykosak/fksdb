<?php

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

class MergedStrategy implements MergeStrategy
{

    /**
     * @param mixed $trunk
     * @param mixed $merged
     * @return mixed
     */
    public function mergeValues($trunk, $merged)
    {
        return $merged;
    }
}
