<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Sous;

use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Modules\Core\Language;

abstract class SousMail extends EventParticipantCallback
{
    final protected function getData(ModelHolder $holder): array
    {
        return [
            'blind_carbon_copy' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
            'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Contest),
            'lang' => Language::from(Language::CS),
        ];
    }
}
