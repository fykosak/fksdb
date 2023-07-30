<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Transitions\Machine\Machine;

/**
 * @template H of FKSDB\Models\Transitions\Holder\ModelHolder
 */
interface TransitionsDecorator
{
    /**
     * @param Machine<H> $machine
     */
    public function decorate(Machine $machine): void;
}
