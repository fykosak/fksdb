<?php

namespace FKSDB\model\Transitions;

use FKSDB\Model\Transitions\Machine\Machine;

/**
 * Class AbstractTransitionsGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITransitionsDecorator {

    public function decorate(Machine $machine): void;
}
