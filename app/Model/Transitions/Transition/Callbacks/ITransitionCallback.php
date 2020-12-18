<?php

namespace FKSDB\Model\Transitions\Transition\Callbacks;

use FKSDB\Model\Transitions\Holder\IModelHolder;

/**
 * Interface ITransitionCallback
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITransitionCallback {

    public function __invoke(IModelHolder $model, ...$args): void;

    public function invoke(IModelHolder $model, ...$args): void;
}
