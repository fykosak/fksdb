<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Dsef;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class ParticipantMailCallback extends EventParticipantCallback
{
    /**
     * @param BaseHolder $holder
     */
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'participant.latte';
    }

    /**
     * @param BaseHolder $holder
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => 'Registrace na DSEF – ' . $holder->getModel()->person->getFullName(),
            'blind_carbon_copy' => 'Den s experimentální fyzikou <dsef@fykos.cz>',
            'sender' => 'Den s experimentální fyzikou <dsef@fykos.cz>',
        ];
    }
}
