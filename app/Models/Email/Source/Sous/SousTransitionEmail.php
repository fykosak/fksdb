<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Sous;

use FKSDB\Models\Email\Source\EventParticipantTransitionEmail;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends EventParticipantTransitionEmail<array{
 *     person: PersonModel,
 *     holder: ParticipantHolder,
 *     model: EventParticipantModel,
 * }>
 */
abstract class SousTransitionEmail extends EventParticipantTransitionEmail
{
    final protected function getData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'recipient_person_id' => $holder->getModel()->person_id,
            'blind_carbon_copy' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
            'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
        ];
    }

    protected function getTemplateData(ParticipantHolder $holder, Transition $transition): array
    {
        return [
            'person' => $holder->getModel()->person,
            'holder' => $holder,
            'model' => $holder->getModel(),
        ];
    }

    protected function getLang(ParticipantHolder $holder, Transition $transition): Language
    {
        return Language::from(Language::CS);
    }
}
