<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Setkani;

use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends EventParticipantCallback<ParticipantHolder>
 */
class ParticipantMailCallback extends EventParticipantCallback
{
    /**
     * @param ParticipantHolder $holder
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'confirmation.latte';
    }

    /**
     * @param ParticipantHolder $holder
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder, Transition $transition): array
    {
        return [
            'subject' => 'Výfučí setkání',
            'blind_carbon_copy' => 'Výfučí setkání <vyfuk@vyfuk.mff.cuni.cz>',
            'sender' => 'Výfučí setkání <vyfuk@vyfuk.mff.cuni.cz>',
        ];
    }
}