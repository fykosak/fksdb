<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Payment;

use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class NewPaymentCallback extends MailCallback
{
    protected function getData(ModelHolder $holder): array
    {
        if ($holder->getModel()->game_lang->value === 'cs') {
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

    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'create';
    }
}
