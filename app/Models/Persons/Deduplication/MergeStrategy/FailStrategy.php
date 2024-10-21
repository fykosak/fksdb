<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @phpstan-template TValue
 * @phpstan-implements MergedStrategy<TValue>
 */
class FailStrategy implements MergeStrategy
{
    /**
     * @phpstan-param TValue $trunk
     * @phpstan-param TValue $merged
     */
    public function mergeValues($trunk, $merged)
    {
        throw new CannotMergeException();
    }
}
