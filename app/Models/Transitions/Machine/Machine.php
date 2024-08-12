<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-template THolder of ModelHolder
 */
abstract class Machine
{
    /** @phpstan-var Transition<THolder>[] */
    public array $transitions = [];

    /**
     * @phpstan-return TransitionsSelection<THolder>
     */
    public function getTransitions(): TransitionsSelection
    {
        return new TransitionsSelection($this->transitions);
    }

    /**
     * @phpstan-return THolder
     */
    abstract public function createHolder(Model $model): ModelHolder;
}
