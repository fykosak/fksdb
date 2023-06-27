<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

class TrunkStrategy implements MergeStrategy
{

    /**
     * @param mixed $trunk
     * @param mixed $merged
     * @return mixed
     */
    public function mergeValues($trunk, $merged)
    {
        return $trunk;
    }
}
