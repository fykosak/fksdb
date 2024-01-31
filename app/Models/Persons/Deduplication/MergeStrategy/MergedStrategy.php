<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @phpstan-template TValue
 * @phpstan-implements MergedStrategy<TValue>
 */
class MergedStrategy implements MergeStrategy
{
    /**
     * @phpstan-param TValue $trunk
     * @phpstan-param TValue $merged
     * @phpstan-return TValue
     */
    public function mergeValues($trunk, $merged)
    {
        return $merged;
    }
}
