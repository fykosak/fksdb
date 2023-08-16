<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Transitions\Machine\Machine;

/**
 * @template THolder of \FKSDB\Models\Transitions\Holder\ModelHolder
 */
interface TransitionsDecorator
{
    /**
     * @param Machine<THolder> $machine
     */
    public function decorate(Machine $machine): void;
}
