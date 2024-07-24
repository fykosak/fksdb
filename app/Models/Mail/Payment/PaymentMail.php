<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Payment;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;

/**
 * @phpstan-extends MailCallback<PaymentModel>
 */
class PaymentMail extends MailCallback
{
    /**
     * @phpstan-return array{
     *     blind_carbon_copy:string,
     *     sender:string,
     * }
     */
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
        ];
    }

    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . MailCallback::resolveLayoutName($transition);
    }
}
