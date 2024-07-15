<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Sous;

use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;

class Reminder2EmailSource extends ReminderEmailSource
{
    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder2.latte';
    }

    public function title(): Title
    {
        return new Title(null, _('Reminder 2'));
    }

    public function description(): LocalizedString//@phpstan-ignore-line
    {
        return new LocalizedString(['cs' => '', 'en' => '']);
    }
}
