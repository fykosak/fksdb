<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template TState of (FakeStringEnum&EnumColumn)|(\BackedEnum&EnumColumn)
 */
interface ModelHolder
{
    /**
     * @phpstan-param TState $newState
     */
    public function setState(EnumColumn $newState): void;

    /**
     * @phpstan-return TState
     */
    public function getState(): EnumColumn;

    /**
     * @phpstan-return TModel
     */
    public function getModel(): Model;
}
