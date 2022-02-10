<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Expressions\EvaluatedExpression;
use Nette\SmartObject;

class RegOpen extends EvaluatedExpression
{
    use SmartObject;
    use WithEventTrait;

    public function __invoke(...$args): bool
    {
        $event = $this->getEvent($args[0]);
        return (!$event->registration_begin || $event->registration_begin->getTimestamp() <= time())
            && (!$event->registration_end || $event->registration_end->getTimestamp() >= time());
    }

    public function __toString(): string
    {
        return 'regOpen';
    }
}
