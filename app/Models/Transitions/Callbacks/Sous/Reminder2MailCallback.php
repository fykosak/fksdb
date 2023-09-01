<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Sous;

use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class Reminder2MailCallback extends EventParticipantCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder2.latte';
    }

    /**
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => 'Podzimní soustředění FYKOSu',
            'blind_carbon_copy' => null,
            'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
        ];
    }
}
