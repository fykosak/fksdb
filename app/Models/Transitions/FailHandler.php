<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-template THolder of ModelHolder
 * @phpstan-import-type Enum from Transition
 */
interface FailHandler
{
    /**
     * @phpstan-param THolder $holder
     * @phpstan-param  Transition<THolder> $transition
     */
    public function handle(\Throwable $exception, ModelHolder $holder, Transition $transition): void;
}
