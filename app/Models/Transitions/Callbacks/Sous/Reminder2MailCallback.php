<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Sous;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends EventParticipantCallback<BaseHolder>
 */
class Reminder2MailCallback extends EventParticipantCallback
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder2.latte';
    }

    /**
     * @phpstan-param Transition<BaseHolder> $transition
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder, Transition $transition): array
    {
        return [
            'subject' => 'Podzimní soustředění FYKOSu',
            'blind_carbon_copy' => null,
            'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
        ];
    }
}
