<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Sous;

use FKSDB\Models\Email\EventParticipantCallback;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends EventParticipantCallback<BaseHolder>
 */
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
