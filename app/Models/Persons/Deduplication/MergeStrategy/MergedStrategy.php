<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @template TValue
 * @phpstan-implements MergedStrategy<TValue>
 */
class MergedStrategy implements MergeStrategy
{
    /**
     * @param TValue $trunk
     * @param TValue $merged
     * @return TValue
     */
    public function mergeValues($trunk, $merged)
    {
        return $merged;
    }
}
