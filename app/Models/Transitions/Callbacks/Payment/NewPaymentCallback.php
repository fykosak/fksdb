<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Payment;

use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\PaymentHolder;

/**
 * @phpstan-extends MailCallback<PaymentHolder>
 */
class NewPaymentCallback extends MailCallback
{
    /**
     * @param PaymentHolder $holder
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        if ($holder->getModel()->person->getPreferredLang() === 'cs') {
            $subject = 'Platba na Fyziklání';
            $sender = 'Fyziklání <fyziklani@fykos.cz>';
        } else {
            $subject = 'Fyziklani Payment';
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
     */
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'create';
    }
}
