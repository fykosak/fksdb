<?php

namespace FKSDB\Transitions;

use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Interface IStateModel
 * *
 */
interface IStateModel {
    /**
     * @param $newState
     * @return void
     */
    public function updateState($newState);

    /**
     * @return string|null
     */
    public function getState();

    /**
     * @param Context $connection
     * @param IConventions $conventions
     * @return IStateModel
     */
    public function refresh(Context $connection, IConventions $conventions): self;
}
