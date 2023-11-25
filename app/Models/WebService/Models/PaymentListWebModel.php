<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
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
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    /**
     * @throws \Exception
     */
    public function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        /** @var PaymentModel $payment */
        foreach ($event->getPayments() as $payment) {
            $paymentData = $payment->__toArray();
            $paymentData['items'] = [];
            /** @var PersonScheduleModel $personSchedule */
            foreach ($payment->getRelatedPersonSchedule() as $personSchedule) {
                $paymentData['items'][] = [
                    'price' => $personSchedule->schedule_item->getPrice()->__serialize(),
                    'itemId' => $personSchedule->schedule_item_id,
                ];
            }
            $data[] = $paymentData;
        }
        return $data;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int')->required(),
        ]);
    }
}
