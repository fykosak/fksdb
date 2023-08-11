<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\InvalidStateException;

/**
 * @template M of \Fykosak\NetteORM\Model
 */
class UnavailableTransitionException extends InvalidStateException
{
    /**
     * @param M|ModelHolder<FakeStringEnum&EnumColumn,M>|null $holder
     * @param Transition<ModelHolder<FakeStringEnum&EnumColumn,M>> $transition
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
                $holder instanceof ModelHolder ? (string)$holder->getModel() : (string)$holder
            )
        );
    }
}
