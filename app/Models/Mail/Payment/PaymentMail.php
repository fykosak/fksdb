<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\Payment;

use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use Nette\InvalidStateException;

/**
 * @phpstan-extends MailCallback<PaymentHolder>
 */
class PaymentMail extends MailCallback
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
            switch (MailCallback::resolveLayoutName($transition)) {
                case 'in_progress->waiting':
                    $subject = 'Platba na Fyziklání';
                    $sender = 'Fyziklání <fyziklani@fykos.cz>';
                    break;
                case 'waiting->received':
                    $subject = 'Potvrzení přijetí platby';
                    $sender = 'Fyziklání <fyziklani@fykos.cz>';
                    break;
                default:
                    throw new InvalidStateException();
            }
        } else {
            switch (MailCallback::resolveLayoutName($transition)) {
                case 'in_progress->waiting':
                    $subject = 'Fyziklani Payment';
                    $sender = 'Fyziklani <fyziklani@fykos.org>';
                    break;
                case 'waiting->received':
                    $subject = 'Payment received';
                    $sender = 'Fyziklani <fyziklani@fykos.org>';
                    break;
                default:
                    throw new InvalidStateException();
            }
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
        return __DIR__ . DIRECTORY_SEPARATOR . MailCallback::resolveLayoutName($transition);
    }
}
