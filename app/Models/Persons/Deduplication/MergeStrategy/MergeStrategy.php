<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @template TValue
 */
interface MergeStrategy
{
    /**
     * @param TValue $trunk
     * @param TValue $merged
     * @return TValue
     * @throws CannotMergeException
     */
    public function mergeValues($trunk, $merged);
}
