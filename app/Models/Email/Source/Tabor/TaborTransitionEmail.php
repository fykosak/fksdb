<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Tabor;

use FKSDB\Models\Email\ParticipantTransitionEmail;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends ParticipantTransitionEmail<array{
 *     model: EventParticipantModel,
 * }>
 */
abstract class TaborTransitionEmail extends ParticipantTransitionEmail
{
    final protected function getTemplateData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'model' => $holder->getModel(),
        ];
    }

    final protected function getLang(ParticipantHolder $holder, Transition $transition): Language
    {
        return Language::from(Language::CS);
    }

    final protected function getData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Vyfuk),
            'lang' => Language::from(Language::CS),
        ];
    }
}
