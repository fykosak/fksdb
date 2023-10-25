<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Payment;

use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends MailCallback<PaymentHolder>
 */
class ReceivedPaymentCallback extends MailCallback
{
    /**
     * @param PaymentHolder $holder
     * @phpstan-param Transition<PaymentHolder> $transition
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder, Transition $transition): array
    {
        if ($holder->getModel()->person->getPreferredLang() === 'cs') {
            $subject = 'Potvrzení přijetí platby';
            $sender = 'Fyziklání <fyziklani@fykos.cz>';
        } else {
            $subject = 'Payment received';
            $sender = 'Fyziklani <fyziklani@fykos.org>';
        }
        return [
            'blind_carbon_copy' => 'Fyziklání <fyziklani@fykos.cz>',
            'sender' => $sender,
            'subject' => $subject,
        ];
    }

    /**
     * @param PaymentHolder $holder
     * @phpstan-param Transition<PaymentHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'receive';
    }
}
