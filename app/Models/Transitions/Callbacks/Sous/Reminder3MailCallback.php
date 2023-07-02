<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Sous;

use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class ReminderMailCallback extends EventParticipantCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder3.latte';
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => 'Podzimní soustředění FYKOSu',
            'blind_carbon_copy' => null,
            'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
        ];
    }
}
