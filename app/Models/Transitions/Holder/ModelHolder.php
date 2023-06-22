<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\NetteORM\Model;

interface ModelHolder
{
    public function updateState(EnumColumn $newState): void;

    public function getState(): EnumColumn;

    public function getModel(): ?Model;
}
