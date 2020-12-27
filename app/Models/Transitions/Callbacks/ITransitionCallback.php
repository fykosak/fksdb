<?php

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Transitions\Holder\IModelHolder;

/**
 * Interface ITransitionCallback
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITransitionCallback {

    public function __invoke(IModelHolder $model, ...$args): void;

    public function invoke(IModelHolder $model, ...$args): void;
}
