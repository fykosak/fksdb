<?php

namespace FKSDB\Transitions;

use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Interface IStateModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IStateModel {
    /**
     * @param string $newState
     * @return void
     */
    public function updateState(string $newState);

    /**
     * @return string|null
     */
    public function getState();

    public function refresh(Context $connection, IConventions $conventions): self;
}
