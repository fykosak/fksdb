<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\PaymentService;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class PaymentListWebModel extends WebModel
{
    private PaymentService $paymentService;

    public function injectService(PaymentService $paymentService): void
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @throws \Exception
     */
    public function getJsonResponse(array $params): array
    {
        $data = [];
        /** @var PaymentModel $payment */
        foreach ($this->paymentService->getTable()->where('event_id', $params['event_id']) as $payment) {
            $paymentData = $payment->__toArray();
            $paymentData['items'] = [];
            /** @var PersonScheduleModel $personSchedule */
            foreach ($payment->getRelatedPersonSchedule() as $personSchedule) {
                $paymentData['items'] [] = [
                    'price' => $personSchedule->schedule_item->getPrice()->__serialize(),
                    'itemName' => $personSchedule->schedule_item->getName(),
                    'description' => $personSchedule->schedule_item->getDescription(),
                    'groupName' => $personSchedule->schedule_item->schedule_group->getName(),
                ];
            }
            $data[] = $paymentData;
        }
        return $data;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'event_id' => Expect::scalar()->castTo('int')->required(),
        ]);
    }
}
