<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\NetteORM\Model;

/**
 * @template S of (\FKSDB\Models\Utils\FakeStringEnum&EnumColumn)
 * @template M of Model
 */
interface ModelHolder
{
    /**
     * @param S $newState
     */
    public function updateState(EnumColumn $newState): void;

    /**
     * @return S $newState
     */
    public function getState(): EnumColumn;

    /**
     * @return M|null
     */
    public function getModel(): ?Model;
}
