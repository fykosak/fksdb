<?php

namespace FKSDB\Models\Transitions;

use Nette\Database\Conventions;
use Nette\Database\Explorer;

/**
 * Interface IStateModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IStateModel {

    public function updateState(string $newState): void;

    public function getState(): ?string;

    public function refresh(Explorer $explorer, Conventions $conventions): self;
}
