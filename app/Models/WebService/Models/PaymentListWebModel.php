<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\PaymentModel;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-import-type SerializedPaymentModel from PaymentModel
 * @phpstan-extends WebModel<array{
 *     eventId:int,
 * },array<(SerializedPaymentModel & array{items:array<array{price:array<string,float>,itemId:int}>})[]>>
 */
class PaymentListWebModel extends WebModel
{
    /**
     * @throws \Exception
     */
    protected function getJsonResponse(): array
    {
        /*  $event = $this->eventService->findByPrimary($params['eventId']);
          if (!$event) {
              throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
          }
          $data = [];
          /** @var PaymentModel $payment
          foreach ($event->getPayments() as $payment) {
              $paymentData = $payment->__toArray();
              $paymentData['items'] = [];
              /** @var SchedulePaymentModel $schedulePayment
              foreach ($payment->getSchedulePayment() as $schedulePayment) {
                  $paymentData['items'][] = [
                      'price' => $schedulePayment->person_schedule->schedule_item->getPrice()->__serialize(),
                      'itemId' => $schedulePayment->person_schedule->schedule_item_id,
                  ];
              }
              $data[] = $paymentData;
          }*/
        return [];
    }

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    protected function isAuthorized(): bool
    {
        return false;
    }
}
