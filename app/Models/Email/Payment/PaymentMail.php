<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Payment;

use FKSDB\Models\Email\TransitionEmail;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;

/**
 * @phpstan-extends TransitionEmail<PaymentHolder>
 */
class PaymentMail extends TransitionEmail
{
    protected function getData(ModelHolder $holder): array
    {
        if ($holder->getModel()->person->getPreferredLang() === 'cs') {
            $sender = 'Fyziklání <fyziklani@fykos.cz>';
        } else {
            $sender = 'Fyziklani <fyziklani@fykos.org>';
        }
        return [
            'blind_carbon_copy' => 'Fyziklání <fyziklani@fykos.cz>',
            'sender' => $sender,
            'topic' => EmailMessageTopic::from(EmailMessageTopic::Internal),
            'lang' => Language::from($holder->getModel()->person->getPreferredLang() ?? Language::EN),
        ];
    }

    /**
     * @param PaymentHolder $holder
     * @phpstan-param Transition<PaymentHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . TransitionEmail::resolveLayoutName($transition);
    }
}
