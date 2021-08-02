<?php

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Transitions\Machine\Machine;

interface TransitionsDecorator
{

    public function decorate(Machine $machine): void;
}
