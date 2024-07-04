<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Sous;

use FKSDB\Models\ORM\Services\EventParticipantService;

class Reminder1Mail extends ReminderMail
{
    protected EventParticipantService $eventParticipantService;

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder1.latte';
    }
}
