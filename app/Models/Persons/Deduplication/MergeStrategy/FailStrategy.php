<?php

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

class FailStrategy implements MergeStrategy
{

    /**
     * @param mixed $trunk
     * @param mixed $merged
     */
    public function mergeValues($trunk, $merged)
    {
        throw new CannotMergeException();
    }
}
