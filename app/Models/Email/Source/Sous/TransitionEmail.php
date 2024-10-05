<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Sous;

use FKSDB\Models\Email\ParticipantTransitionEmail;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
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
    final protected function getData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'blind_carbon_copy' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
            'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Fykos),
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

    protected function getTemplatePath(ParticipantHolder $holder, Transition $transition): string
    {
        switch ($transition->source->value) {
            case EventParticipantStatus::INIT:
            case EventParticipantStatus::AUTO_SPARE:
            case EventParticipantStatus::AUTO_INVITED:
                return __DIR__ . DIRECTORY_SEPARATOR . 'init->invite.latte';
            default:
                return __DIR__ . DIRECTORY_SEPARATOR . self::resolveLayoutName($transition);
        }
    }
}
