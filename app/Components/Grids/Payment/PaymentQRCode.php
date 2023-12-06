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

    /**
     * @phpstan-return array{
     * paymentId:int,
     * price:float|null,
     * currency:string|null,
     * constantSymbol:string|null,
     * variableSymbol:string|null,
     * specificSymbol:string|null,
     * recipient:string|null,
     * iban:string|null,
     * swift:string|null,
     * }
     */
    protected function getData(): array
    {
        return [
            'paymentId' => $this->payment->payment_id,
            'price' => $this->payment->price,
            'currency' => $this->payment->currency,
            'constantSymbol' => $this->payment->constant_symbol,
            'variableSymbol' => $this->payment->variable_symbol,
            'specificSymbol' => $this->payment->specific_symbol,
            'recipient' => $this->payment->recipient,
            'iban' => $this->payment->iban,
            'swift' => $this->payment->swift,
        ];
    }
}
