<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Dsef;

use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends EventParticipantCallback<ParticipantHolder>
 */
class ParticipantMailCallback extends EventParticipantCallback
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'participant.latte';
    }

    /**
     * @phpstan-return array{
     *     blind_carbon_copy?:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'blind_carbon_copy' => 'Den s experimentální fyzikou <dsef@fykos.cz>',
            'sender' => 'Den s experimentální fyzikou <dsef@fykos.cz>',
        ];
    }
}
