<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

class MergedStrategy implements MergeStrategy
{
    /**
     * @template T
     * @param T $trunk
     * @param T $merged
     * @return T
     */
    public function mergeValues($trunk, $merged)
    {
        return $merged;
    }
}
