<?php

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Models\AbstractModelSingle;

interface IModelHolder {

    public function updateState(string $newState): IModelHolder;

    public function getState(): string;

    public function getModel(): ?AbstractModelSingle;
}
