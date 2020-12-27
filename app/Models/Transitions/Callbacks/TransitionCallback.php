<?php

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Transitions\Holder\ModelHolder;

/**
 * Interface ITransitionCallback
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface TransitionCallback {

    public function __invoke(ModelHolder $holder, ...$args): void;

    public function invoke(ModelHolder $holder, ...$args): void;
}
