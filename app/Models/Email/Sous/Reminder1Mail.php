<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Sous;

use FKSDB\Models\ORM\Services\EventParticipantService;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;

class Reminder1Mail extends ReminderMail
{
    protected EventParticipantService $eventParticipantService;

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder1.latte';
    }

    public function title(): Title
    {
        return new Title(null, _('Reminder 1'));
    }

    public function description(): LocalizedString//@phpstan-ignore-line
    {
        return new LocalizedString(['cs' => '', 'en' => '']);
    }
}
