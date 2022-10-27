<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Setkani;

use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class ParticipantMailCallback extends EventParticipantCallback
{

    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'confirmation.latte';
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => 'Výfučí setkání',
            'blind_carbon_copy' => 'Výfučí setkání <vyfuk@vyfuk.mff.cuni.cz>',
            'sender' => 'Výfučí setkání <vyfuk@vyfuk.mff.cuni.cz>',
        ];
    }
}
