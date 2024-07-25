<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Dsef;

use FKSDB\Models\Email\Source\EventParticipantCallback;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

class ParticipantMailCallback extends EventParticipantCallback
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'participant.latte';
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'blind_carbon_copy' => 'Den s experimentální fyzikou <dsef@fykos.cz>',
            'sender' => 'Den s experimentální fyzikou <dsef@fykos.cz>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::DSEF),
            'lang' => Language::from(Language::CS),
        ];
    }
}
