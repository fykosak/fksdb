<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Tabor;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends EventParticipantCallback<BaseHolder>
 */
class AppliedInterestedMailCallback extends EventParticipantCallback
{
    /**
     * @param BaseHolder $holder
     * @phpstan-param Transition<BaseHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'applied_interested.latte';
    }

    /**
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
