<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Setkani;

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
        return __DIR__ . DIRECTORY_SEPARATOR . 'confirmation.latte';
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
            'subject' => 'Výfučí setkání',
            'blind_carbon_copy' => 'Výfučí setkání <vyfuk@vyfuk.mff.cuni.cz>',
            'sender' => 'Výfučí setkání <vyfuk@vyfuk.mff.cuni.cz>',
        ];
    }
}
