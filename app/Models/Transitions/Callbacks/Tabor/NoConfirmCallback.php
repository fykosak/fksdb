<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Tabor;

use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

class NoConfirmMailCallback extends EventParticipantCallback
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'no_confirm.latte';
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Contest),
            'lang' => Language::from(Language::CS),
        ];
    }
}
