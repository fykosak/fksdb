<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Sous;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;

class InterestedAppliedMailCallback extends SousMail
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'interested_applied.latte';
    }
}
