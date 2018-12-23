<?php


namespace FKSDB\ORM\Services;

use AbstractServiceSingle;
use DbNames;
use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\Handler\DuplicateAccommodationPaymentException;
use FKSDB\Payment\Handler\EmptyDataException;
use Nette\ArrayHash;
use Nette\Diagnostics\Debugger;

class ServicePaymentAccommodation extends AbstractServiceSingle {
    protected $tableName = DbNames::TAB_PAYMENT_ACCOMMODATION;
    protected $modelClassName = 'FKSDB\ORM\ModelPaymentAccommodation';

    /**
     * @param ArrayHash $data
     * @param ModelPayment $payment
     * @throws DuplicateAccommodationPaymentException
     * @throws EmptyDataException
     */
    public function prepareAndUpdate($data, ModelPayment $payment) {
        $oldRows = $this->getTable()->where('payment_id', $payment->payment_id);
        // $payment->getRelatedPersonAccommodation();

        $newAccommodationIds = $this->prepareData($data);
        /* if (count($newAccommodationIds) == 0) {
             throw new EmptyDataException(_('Nebola vybraná žiadá položka'));
         };*/
        /**
         * @var $row \FKSDB\ORM\ModelPaymentAccommodation
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
        $this->connection->beginTransaction();
        foreach ($newAccommodationIds as $id) {

            /**
             * @var $model \FKSDB\ORM\ModelPaymentAccommodation
             */
            $model = $this->createNew(['payment_id' => $payment->payment_id, 'event_person_accommodation_id' => $id]);
            $count = $this->getTable()->where('event_person_accommodation_id', $id)->where('payment.state !=? OR payment.state IS NULL', ModelPayment::STATE_CANCELED)->count();
            if ($count > 0) {
                $this->connection->rollBack();
                throw new DuplicateAccommodationPaymentException(sprintf(
                    _('Ubytovanie "%s" má už vygenrovanú inú platbu.'),
                    $model->getEventPersonAccommodation()->getLabel()
                ));
            }
            $this->save($model);
        }
        $this->connection->commit();
    }

    /**
     * @param ArrayHash $data
     * @return integer[]
     */
    private function prepareData($data): array {
        $data = (array)json_decode($data);
        Debugger::barDump($data);
        return \array_keys(\array_filter($data, function ($value) {
            return $value;
        }));
    }
}
