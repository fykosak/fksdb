<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use Fykosak\NetteORM\AbstractModel;

interface ModelHolder
{
    public function updateState(string $newState): void;

    public function getState(): string;

    public function getModel(): ?AbstractModel;

    public function updateData(array $data): void;
}
