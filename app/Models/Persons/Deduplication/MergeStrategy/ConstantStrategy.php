<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @template TValue
 * @phpstan-implements MergedStrategy<TValue>
 */
class ConstantStrategy implements MergeStrategy
{
    /** @var TValue */
    private $constant;

    /**
     * @param TValue $constant
     */
    public function __construct($constant)
    {
        $this->constant = $constant;
    }

    /**
     * @param TValue $trunk
     * @param TValue $merged
     * @phpstan-return TValue
     */
    public function mergeValues($trunk, $merged)
    {
        return $this->constant;
    }
}
