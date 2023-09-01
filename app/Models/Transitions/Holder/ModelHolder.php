<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\NetteORM\Model;

/**
 * @phpstan-template TState of (\FKSDB\Models\Utils\FakeStringEnum&EnumColumn)
 * @phpstan-template TModel of Model
 */
interface ModelHolder
{
    /**
     * @phpstan-param TState $newState
     */
    public function updateState(EnumColumn $newState): void;

    /**
     * @phpstan-return TState $newState
     */
    public function getState(): EnumColumn;

    /**
     * @phpstan-return TModel|null
     */
    public function getModel(): ?Model;
}
