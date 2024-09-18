<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Setkani;

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
final class TransitionEmail extends ParticipantTransitionEmail
{
    protected function getTemplatePath(ParticipantHolder $holder, Transition $transition): string
    {
        $transitionId = self::resolveLayoutName($transition);
        return __DIR__ . DIRECTORY_SEPARATOR . "$transitionId.latte";
    }

    protected function getData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Internal),
            'lang' => Language::from(Language::CS),
        ];
    }

    protected function getTemplateData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'model' => $holder->getModel(),
        ];
    }

    protected function getLang(ParticipantHolder $holder, Transition $transition): Language
    {
        return Language::from(Language::CS);
    }
}
