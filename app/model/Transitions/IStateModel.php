<?php

namespace FKSDB\Transitions;

use FKSDB\ORM\IModel;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Interface IStateModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IStateModel extends IModel {

    public function updateState(string $newState): void;

    public function getState(): ?string;

    public function refresh(Context $connection, IConventions $conventions): self;
}
