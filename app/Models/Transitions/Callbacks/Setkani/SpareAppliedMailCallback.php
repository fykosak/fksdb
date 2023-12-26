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
class SpareAppliedMailCallback extends EventParticipantCallback
{
    /**
     * @param ParticipantHolder $holder
     * @phpstan-param Transition<ParticipantHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'spareApplied.latte';
    }

    /**
     * @param ParticipantHolder $holder
     * @phpstan-return array{
     *     blind_carbon_copy?:string,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => 'Výfučí setkání',
            'blind_carbon_copy' => 'Výfučí setkání <vyfuk@vyfuk.org>',
            'sender' => 'Výfučí setkání <vyfuk@vyfuk.org>',
        ];
    }
}
