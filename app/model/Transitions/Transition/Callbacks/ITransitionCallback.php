<?php

namespace FKSDB\model\Transitions\Transition\Callbacks;

use FKSDB\Model\Transitions\IStateModel;

/**
 * Interface ITransitionCallback
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITransitionCallback {

    public function __invoke(IStateModel $model, ...$args): void;

    public function invoke(IStateModel $model, ...$args): void;
}