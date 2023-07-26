<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

/**
 * @phpstan-template T
 * @phpstan-template P
 */
interface Statement
{
    /**
     * @phpstan-return T
     * @phpstan-param P $args
     */
    public function __invoke(...$args);
}
