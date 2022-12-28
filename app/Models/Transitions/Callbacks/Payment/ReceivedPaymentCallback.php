<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Payment;

use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class ReceivedPaymentCallback extends MailCallback
{
    protected function getData(ModelHolder $holder): array
    {
        if ($holder->getModel()->game_lang->value === 'cs') {
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

    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'receive';
    }
}
