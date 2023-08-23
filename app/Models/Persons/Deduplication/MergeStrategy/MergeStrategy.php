<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @phpstan-template TValue
 */
interface MergeStrategy
{
    /**
     * @phpstan-param TValue $trunk
     * @phpstan-param TValue $merged
     * @phpstan-return TValue
     * @throws CannotMergeException
     */
    public function mergeValues($trunk, $merged);
}
