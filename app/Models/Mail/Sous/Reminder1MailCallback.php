<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Sous;

use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;

class Reminder1MailCallback extends SousMail
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder1.latte';
    }
}
