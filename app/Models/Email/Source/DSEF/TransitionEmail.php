<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\DSEF;

use FKSDB\Models\Email\ParticipantTransitionEmail;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends ParticipantTransitionEmail<array{
 *     model: EventParticipantModel,
 *     token: AuthTokenModel,
 * }>
 */
final class TransitionEmail extends ParticipantTransitionEmail
{
    protected function getTemplatePath(ParticipantHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'participant.latte';
    }

    protected function getData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'blind_carbon_copy' => 'Den s experimentální fyzikou <dsef@fykos.cz>',
            'sender' => 'Den s experimentální fyzikou <dsef@fykos.cz>',
            'topic' => EmailMessageTopic::DSEF,
            'lang' => Language::from(Language::CS),
        ];
    }

    /**
     * @throws \Throwable
     */
    protected function getTemplateData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'model' => $holder->getModel(),
            'token' => $this->createToken($holder),
        ];
    }

    protected function getLang(ParticipantHolder $holder, Transition $transition): Language
    {
        return Language::from(Language::CS);
    }
}
