<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Sous;

use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;

class Reminder3Mail extends ReminderMail
{
    protected function getTemplateFile($source): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder3.latte';
    }

    public function title(): Title
    {
        return new Title(null, _('Reminder 3'));
    }

    public function description(): LocalizedString //@phpstan-ignore-line
    {
        return new LocalizedString(['cs' => '', 'en' => '']);
    }
}
