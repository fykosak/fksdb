<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @phpstan-template TValue
 * @phpstan-implements MergedStrategy<TValue>
 */
class ConstantStrategy implements MergeStrategy
{
    /** @phpstan-var TValue */
    private $constant;

    /**
     * @phpstan-param TValue $constant
     */
    public function __construct($constant)
    {
        $this->constant = $constant;
    }

    /**
     * @phpstan-param TValue $trunk
     * @phpstan-param TValue $merged
     * @phpstan-return TValue
     */
    public function mergeValues($trunk, $merged)
    {
        return $this->constant;
    }
}
