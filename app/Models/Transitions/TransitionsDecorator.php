<?php

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Transitions\Machine\Machine;

/**
 * Class AbstractTransitionsGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface TransitionsDecorator {

    public function decorate(Machine $machine): void;
}
