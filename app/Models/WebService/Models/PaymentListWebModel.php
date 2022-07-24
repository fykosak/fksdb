<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\ServicePayment;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class PaymentListWebModel extends WebModel
{
    private ServicePayment $servicePayment;

    public function injectService(ServicePayment $servicePayment): void
    {
        $this->servicePayment = $servicePayment;
    }

    public function getJsonResponse(array $params): array
    {
        $data = [];
        /** @var PaymentModel $payment */
        foreach ($this->servicePayment->getTable()->where('event_id', $params['event_id']) as $payment) {
            $data[] = $payment->__toArray();
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
