<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Payment;

use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class ReceivedPaymentCallback extends MailCallback
{
    protected function getData(ModelHolder $holder): array
    {
        return [
            'blind_carbon_copy' => 'Fyziklání <fyziklani@fykos.cz>',
            'sender' => 'fyziklani@fykos.cz',
            'subject' => 'We are receive payment',
        ];
    }

    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'receive';
    }
}
