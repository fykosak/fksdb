<?php

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Transitions\Machine\Machine;

/**
 * Class AbstractTransitionsGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITransitionsDecorator {

    public function decorate(Machine $machine): void;
}
