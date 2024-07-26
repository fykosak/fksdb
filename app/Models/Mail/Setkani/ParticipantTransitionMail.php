<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Setkani;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends MailCallback<EventParticipantModel>
 */
class ParticipantTransitionMail extends MailCallback
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        $transitionId = self::resolveLayoutName($transition);
        return __DIR__ . DIRECTORY_SEPARATOR . "$transitionId.latte";
    }

    /**
     * @param ParticipantHolder $holder
     * @phpstan-return array{
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
        ];
    }
}
