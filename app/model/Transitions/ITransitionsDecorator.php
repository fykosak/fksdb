<?php

namespace FKSDB\Transitions;

/**
 * Class AbstractTransitionsGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITransitionsDecorator {

    public function decorate(Machine $machine): void;
}
