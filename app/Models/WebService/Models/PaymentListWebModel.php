<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-type SerializedPaymentModel array{
 *      personId:int,
 *      paymentId:int,
 *      state:string,
 *      price:float|null,
 *      currency:string|null,
 *      constantSymbol:string|null,
 *      variableSymbol:string|null,
 *      specificSymbol:string|null,
 *      bankAccount:string|null,
 *      bankName:string|null,
 *      recipient:string|null,
 *      iban:string|null,
 *      swift:string|null,
 * }
 * @phpstan-extends WebModel<array{},
 *     array<(SerializedPaymentModel & array{items:array<array{price:array<string,float>,itemId:int}>})[]>>
 */
class PaymentListWebModel extends WebModel
{
    private PaymentService $paymentService;

    public function inject(PaymentService $paymentService): void
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @throws \Exception
     */
    protected function getJsonResponse(): array
    {
        $data = [];
        /** @var PaymentModel $payment */
        foreach ($this->paymentService->getTable() as $payment) {
            $paymentData = [
                'personId' => $payment->person_id,
                'paymentId' => $payment->payment_id,
                'state' => $payment->state->value,
                'price' => $payment->price,
                'currency' => $payment->currency,
                'constantSymbol' => $payment->constant_symbol,
                'variableSymbol' => $payment->variable_symbol,
                'specificSymbol' => $payment->specific_symbol,
                'bankAccount' => $payment->bank_account,
                'bankName' => $payment->bank_name,
                'recipient' => $payment->recipient,
                'iban' => $payment->iban,
                'swift' => $payment->swift,
            ];
            $paymentData['items'] = [];
            /** @var SchedulePaymentModel $schedulePayment */
            foreach ($payment->getSchedulePayment() as $schedulePayment) {
                $paymentData['items'][] = [
                    'price' => $schedulePayment->person_schedule->schedule_item->getPrice()->__serialize(),
                    'itemId' => $schedulePayment->person_schedule->schedule_item_id,
                ];
            }
            $data[] = $paymentData;
        }
        return $data;
    }

    protected function getInnerExpectedStructure(): array
    {
        return [];
    }

    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowedAnyContest(RestApiPresenter::RESOURCE_ID, self::class);
    }
}
