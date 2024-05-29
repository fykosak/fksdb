<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-template TState of (\FKSDB\Models\Utils\FakeStringEnum&EnumColumn)
 * @phpstan-template TModel of Model
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
