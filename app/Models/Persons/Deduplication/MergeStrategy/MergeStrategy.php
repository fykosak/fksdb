<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

interface MergeStrategy
{

    /**
     * @template T
     * @param T $trunk
     * @param T $merged
     * @throws CannotMergeException
     * @return T
     */
    public function mergeValues($trunk, $merged);
}
