<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

/**
 * @phpstan-template TValue
 * @phpstan-implements MergedStrategy<TValue>
 */
class CummulativeStrategy implements MergeStrategy
{
    private ?string $precedence;

    public function __construct(?string $precedence = null)
    {
        $this->precedence = $precedence;
    }

    /**
     * @phpstan-param TValue $trunk
     * @phpstan-param TValue $merged
     * @phpstan-return TValue
     */
    public function mergeValues($trunk, $merged)
    {
        if ($merged === null) {
            return $trunk;
        }
        if ($trunk === null) {
            return $merged;
        }
        if ($this->equals($trunk, $merged)) {
            return $trunk;
        }

        if ($this->precedence == 'trunk') {
            return $trunk;
        } elseif ($this->precedence == 'merged') {
            return $merged;
        }

        throw new CannotMergeException();
    }

    /**
     * @phpstan-param TValue $trunk
     * @phpstan-param TValue $merged
     */
    private function equals($trunk, $merged): bool
    {
        if ($trunk instanceof \DateTime && $merged instanceof \DateTime) {
            return $trunk->getTimestamp() == $merged->getTimestamp();
        } else {
            return $trunk == $merged;
        }
    }
}
