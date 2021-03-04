<?php

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Models\AbstractModelSingle;

interface ModelHolder {

    public function updateState(string $newState): void;

    public function getState(): string;

    public function getModel(): ?AbstractModelSingle;

    public function updateData(array $data): void;
}
