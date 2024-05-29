<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Sous;

class Reminder2Mail extends ReminderMail
{
    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder2.latte';
    }
}
