<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source;

use FKSDB\Models\Email\TemplateFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-import-type TRenderedData from TemplateFactory
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
    protected EmailMessageService $emailMessageService;

    public function injectSecondary(EmailMessageService $emailMessageService): void
    {
        $this->emailMessageService = $emailMessageService;
    }

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
        foreach ($this->createEmails(['holder' => $holder, 'transition' => $transition]) as $email) {//@phpstan-ignore-line
            $this->emailMessageService->addMessageToSend($email);
        }
    }

    /**
     * @template TStaticHolder of ModelHolder
     * @phpstan-param  Transition<TStaticHolder> $transition
     */
    public static function resolveLayoutName(Transition $transition): string
    {
        return $transition->source->value . '->' . $transition->target->value;
    }

    public function getExpectedParams(): array
    {
        throw new NotImplementedException();
    }

    public function title(): Title
    {
        throw new NotImplementedException();
    }

    public function description(): LocalizedString//@phpstan-ignore-line
    {
        throw new NotImplementedException();
    }
}
