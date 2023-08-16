<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

/**
 * @template TGlobalReturn
 * @template TArgType
 */
interface Statement
{
    /**
     * @phpstan-return TGlobalReturn
     * @phpstan-param TArgType ...$args
     */
    public function __invoke(...$args);
}
