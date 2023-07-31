<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

/**
 * @phpstan-template R
 * @phpstan-template P
 */
interface Statement
{
    /**
     * @phpstan-return R
     * @phpstan-param P ...$args
     */
    public function __invoke(...$args);
}
