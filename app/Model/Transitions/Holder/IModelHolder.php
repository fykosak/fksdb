<?php

namespace FKSDB\Model\Transitions\Holder;

use FKSDB\Model\ORM\Models\AbstractModelSingle;

interface IModelHolder {

    public function updateState(string $newState): IModelHolder;

    public function getState(): string;

    public function getModel(): ?AbstractModelSingle;
}
