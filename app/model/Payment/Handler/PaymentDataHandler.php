<?php

namespace FKSDB\Payment\Handler;

use FKSDB\ORM\ModelPayment;
use FKSDB\ORM\ModelPaymentAccommodation;
use Nette\ArrayHash;
use Submits\StorageException;

class PaymentDataHandler {
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;

    public function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    /**
     * @param ArrayHash $data
     * @param ModelPayment $payment
     * @throws StorageException
     */
    public function prepareAndUpdate(ArrayHash $data, ModelPayment $payment) {
        $oldRows = $payment->getRelatedPersonAccommodation();

        $newAccommodationIds = $this->prepareData($data);
        /**
         * @var $row ModelPaymentAccommodation
         */
        foreach ($oldRows as $row) {
            if (in_array($row->event_person_accommodation_id, $newAccommodationIds)) {
                // do nothing
                $index = array_search($row->event_person_accommodation_id, $newAccommodationIds);
                unset($newAccommodationIds[$index]);
            } else {
                $row->delete();
            }
        }
        foreach ($newAccommodationIds as $id) {
            try {
                /**
                 * @var $model ModelPaymentAccommodation
                 */
                $model = $this->serviceEventPersonAccommodation->createNew(['payment_id' => $payment->payment_id, 'event_person_accommodation_id' => $id]);
                $this->serviceEventPersonAccommodation->save($model);
            } catch (\ModelException $e) {
                if ($e->getPrevious() && $e->getPrevious()->getCode() == 23000) {
                    throw new StorageException(sprintf(
                        _('Item "%s" has already generated payment.'),
                        $model->getEventPersonAccommodation()->getLabel()
                    ));
                }
                throw $e;
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
