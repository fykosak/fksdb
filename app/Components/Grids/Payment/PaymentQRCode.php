<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Models\ORM\Models\PaymentModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class PaymentQRCode extends FrontEndComponent
{
    private PaymentModel $payment;

    public function __construct(Container $container, PaymentModel $model)
    {
        parent::__construct($container, 'payment.qrcode');
        $this->payment = $model;
    }

    protected function getData(): array
    {
        return [
            'paymentId' => $this->payment->payment_id,
            'price' => $this->payment->price,
            'currency' => $this->payment->currency,
            'constantSymbol' => $this->payment->constant_symbol,
            'variableSymbol' => $this->payment->variable_symbol,
            'specificSymbol' => $this->payment->specific_symbol,
            'bankAccount' => $this->payment->bank_account,
            'bankName' => $this->payment->bank_name,
            'recipient' => $this->payment->recipient,
            'iban' => $this->payment->iban,
            'swift' => $this->payment->swift,
        ];
    }
}
