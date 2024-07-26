<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\Email\Source\EmailSource;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template TTemplateParam of array
 * @phpstan-type THolder = ModelHolder<TModel,(FakeStringEnum&EnumColumn)>
 * @phpstan-implements Statement<void,THolder|Transition<THolder>>
 * @phpstan-extends EmailSource<TTemplateParam,array{
 *     holder:THolder,
 *     transition:Transition<THolder>,
 * }>
 */
abstract class TransitionEmailSource extends EmailSource implements Statement
{
    /**
     * @phpstan-param THolder|Transition<THolder> $args
     * @throws BadTypeException
     */
    final public function __invoke(...$args): void
    {
        /**
         * @phpstan-var THolder $holder
         * @phpstan-var Transition<THolder> $transition
         */
        [$holder, $transition] = $args;
        $this->createAndSend(['holder' => $holder, 'transition' => $transition]);
    }

    /**
     * @template TStaticHolder of ModelHolder
     * @phpstan-param  Transition<TStaticHolder> $transition
     */
    public static function resolveLayoutName(Transition $transition): string
    {
        return $transition->source->value . '->' . $transition->target->value;
    }
}
