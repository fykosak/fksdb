<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Tabor;

use FKSDB\Models\Email\EventParticipantCallback;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

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

    protected function getData(ModelHolder $holder): array
    {
        return [
            'sender' => 'Výfuk <vyfuk@vyfuk.org>',
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Contest),
            'lang' => Language::from(Language::CS),
        ];
    }
}
