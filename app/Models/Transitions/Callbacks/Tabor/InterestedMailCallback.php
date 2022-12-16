<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Tabor;

use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class InterestedMailCallback extends EventParticipantCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'interested.latte';
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => 'Letní tábor Výfuku',
            'blind_carbon_copy' => 'Letní tábor Výfuku <vyfuk@vyfuk.mff.cuni.cz>',
            'sender' => 'Výfuk <vyfuk@vyfuk.mff.cuni.cz>',
        ];
    }
}
