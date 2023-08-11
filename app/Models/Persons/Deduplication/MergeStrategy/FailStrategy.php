<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @template TValue
 * @phpstan-implements MergedStrategy<TValue>
 */
class FailStrategy implements MergeStrategy
{

    /**
     * @param TValue $trunk
     * @param TValue $merged
     */
    public function mergeValues($trunk, $merged)
    {
        throw new CannotMergeException();
    }
}
