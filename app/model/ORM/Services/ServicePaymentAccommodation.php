<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Payment\Handler\DuplicateAccommodationPaymentException;
use Nette\ArrayHash;
use Submits\StorageException;

/**
 * Class ServicePaymentAccommodation
 * @package FKSDB\ORM\Services
 * @deprecated
 */
class ServicePaymentAccommodation extends AbstractServiceSingle {
    protected $tableName = DbNames::TAB_PAYMENT_ACCOMMODATION;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelPaymentAccommodation';

    /**
     * @param ArrayHash $data
     * @param ModelPayment $payment
     * @throws DuplicateAccommodationPaymentException
     * @throws \Exception
     */
    public function prepareAndUpdate($data, ModelPayment $payment) {
        $oldRows = $this->getTable()->where('payment_id', $payment->payment_id);
        // $payment->getRelatedPersonAccommodation();

        $newAccommodationIds = $this->prepareData($data);
        /* if (count($newAccommodationIds) == 0) {
             throw new EmptyDataException(_('Nebola vybraná žiadá položka'));
         };*/
        /**
         * @var \FKSDB\ORM\Models\ModelPaymentAccommodation $row
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
        if (!$this->connection->inTransaction()) {
            throw new StorageException(_('Not in transaction!'));
        }
        foreach ($newAccommodationIds as $id) {

            /**
             * @var \FKSDB\ORM\Models\ModelPaymentAccommodation $model
             */
            $model = $this->createNew(['payment_id' => $payment->payment_id, 'event_person_accommodation_id' => $id]);
            $count = $this->getTable()->where('event_person_accommodation_id', $id)->where('payment.state !=? OR payment.state IS NULL', ModelPayment::STATE_CANCELED)->count();
            if ($count > 0) {
                throw new DuplicateAccommodationPaymentException(sprintf(
                    _('Ubytovanie "%s" má už vygenrovanú inú platbu.'),
                    $model->getEventPersonAccommodation()->getLabel()
                ));
            }
            $this->save($model);
        }

    }

    /**
     * @param ArrayHash $data
     * @return integer[]
     */
    private function prepareData($data): array {
        $data = (array)json_decode($data);
        return \array_keys(\array_filter($data, function ($value) {
            return $value;
        }));
    }
}
