<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

/**
 * @phpstan-template GlobalReturn
 * @phpstan-template ArgType
 */
interface Statement
{
    /**
     * @phpstan-return GlobalReturn
     * @phpstan-param ArgType ...$args
     */
    public function __invoke(...$args);
}
