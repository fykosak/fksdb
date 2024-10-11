<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\InvalidStateException;

/**
 * @phpstan-template THolder of ModelHolder
 */
class UnavailableTransitionException extends InvalidStateException
{
    /**
     * @phpstan-param THolder|null $holder
     * @phpstan-param Transition<THolder> $transition
     */
    public function __construct(Transition $transition, ?ModelHolder $holder)
    {
        $source = $transition->source->value;
        $target = $transition->target->value;
        parent::__construct(
            sprintf(
                _('Transition from %s to %s is unavailable for %s'),
                $source,
                $target,
                (string)$holder->getModel()
            )
        );
    }
}
