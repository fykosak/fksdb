<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Expressions\EvaluatedExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\SmartObject;

class RegOpen extends EvaluatedExpression
{
    use SmartObject;

    /**
     * @param BaseHolder $holder
     */
    public function __invoke(ModelHolder $holder): bool
    {
        return (!$holder->event->registration_begin || $holder->event->registration_begin->getTimestamp() <= time())
            && (!$holder->event->registration_end || $holder->event->registration_end->getTimestamp() >= time());
    }

    public function __toString(): string
    {
        return 'regOpen';
    }
}
