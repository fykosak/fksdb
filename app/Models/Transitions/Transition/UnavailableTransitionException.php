<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Machine\Transition as EventTransition;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\NetteORM\Model;
use Nette\InvalidStateException;

class UnavailableTransitionException extends InvalidStateException
{
    /**
     * @param Model|ModelHolder|null $holder
     */
    public function __construct(Transition $transition, $holder)
    {
        $source = $transition->source->value;
        $target = $transition->target->value;
        parent::__construct(
            sprintf(
                _('Transition from %s to %s is unavailable for %s'),
                $source,
                $target,
                $holder instanceof ModelHolder ? $holder->getModel() : $holder
            )
        );
    }
}
