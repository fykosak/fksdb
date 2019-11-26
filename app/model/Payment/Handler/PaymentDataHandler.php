<?php

namespace FKSDB\Payment\Handler;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\Schedule\ModelSchedulePayment;
use FKSDB\ORM\Services\Schedule\ServiceSchedulePayment;
use FKSDB\Submits\StorageException;
use Nette\Utils\ArrayHash;

/**
 * Class PaymentDataHandler
 * @package FKSDB\Payment\Handler
 */
class PaymentDataHandler {
    /**
     * @var ServiceSchedulePayment
     */
    private $serviceSchedulePayment;

    /**
     * PaymentDataHandler constructor.
     * @param ServiceSchedulePayment $serviceSchedulePayment
     */
    public function __construct(ServiceSchedulePayment $serviceSchedulePayment) {
        $this->serviceSchedulePayment = $serviceSchedulePayment;
    }

    /**
     * @param ArrayHash $data
     * @param ModelPayment $payment
     * @throws \Exception
     */
    public function prepareAndUpdate(ArrayHash $data, ModelPayment $payment) {
        $oldRows = $payment->getRelatedPersonSchedule();

        $newScheduleIds = $this->prepareData($data);
        foreach ($oldRows as $row) {
            if (in_array($row->person_schedule_id, $newScheduleIds)) {
                // do nothing
                $index = array_search($row->person_schedule_id, $newScheduleIds);
                unset($newScheduleIds[$index]);
            } else {
                $row->delete();
            }
        }
        foreach ($newScheduleIds as $id) {
            try {
                /**
                 * @var ModelSchedulePayment $model
                 */
                $model = $this->serviceSchedulePayment->createNewModel(['payment_id' => $payment->payment_id, 'person_schedule_id' => $id]);
            } catch (\ModelException $exception) {
                if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                    throw new StorageException(sprintf(
                        _('Item "%s" has already generated payment.'),
                        $model->getPersonSchedule()->getLabel()
                    ));
                }
                throw $exception;
            }
        }
    }

    /**
     * @param ArrayHash $data
     * @return integer[]
     */
    private function prepareData(ArrayHash $data): array {
        $data = (array)json_decode($data);
        return \array_keys(\array_filter($data, function ($value) {
            return $value;
        }));
    }
}
