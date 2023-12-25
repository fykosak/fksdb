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
class Reminder3MailCallback extends EventParticipantCallback
{
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'reminder3.latte';
    }

    /**
     * @phpstan-return array{
     *     blind_carbon_copy?:string,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => 'Podzimní soustředění FYKOSu',
            'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
        ];
    }
}
