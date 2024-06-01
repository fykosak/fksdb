<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Nette\InvalidStateException;

/**
 * @phpstan-template TModel of Model
 */
class UnavailableTransitionException extends InvalidStateException
{
    /**
     * @phpstan-param TModel|ModelHolder<TModel,(FakeStringEnum&EnumColumn)>|null $holder
     * @phpstan-param Transition<ModelHolder<TModel,(FakeStringEnum&EnumColumn)>> $transition
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
