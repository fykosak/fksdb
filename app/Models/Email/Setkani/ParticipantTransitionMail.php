<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Setkani;

use FKSDB\Models\Email\TransitionEmail;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmail<ParticipantHolder>
 */
class ParticipantTransitionMail extends TransitionEmail
{
    /**
     * @param ParticipantHolder $holder
     * @phpstan-param Transition<ParticipantHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        $transitionId = self::resolveLayoutName($transition);
        return __DIR__ . DIRECTORY_SEPARATOR . "$transitionId.latte";
    }

    /**
     * @param ParticipantHolder $holder
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Contest),
            'lang' => Language::from(Language::CS),
        ];
    }
}
