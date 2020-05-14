<?php


namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\Schedule\ModelSchedulePayment;
use FKSDB\Payment\Handler\DuplicatePaymentException;
use FKSDB\Submits\StorageException;
use Nette\Utils\ArrayHash;

/**
 * Class ServiceSchedulePayment
 * @package FKSDB\ORM\Services\Schedule
 */
class ServiceSchedulePayment extends AbstractServiceSingle {

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_SCHEDULE_PAYMENT;
    }

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelSchedulePayment::class;
    }


    /**
     * @param ArrayHash $data
     * @param ModelPayment $payment
     * @throws DuplicatePaymentException
     * @throws \Exception
     */
    public function prepareAndUpdate($data, ModelPayment $payment) {
        $oldRows = $this->getTable()->where('payment_id', $payment->payment_id);

        $newScheduleIds = $this->prepareData($data);
        /* if (count($newScheduleIds) == 0) {
             throw new EmptyDataException(_('Nebola vybraná žiadá položka'));
         };*/
        /**
         * @var ModelSchedulePayment $row
         */
        foreach ($oldRows as $row) {
            if (in_array($row->person_schedule_id, $newScheduleIds)) {
                // do nothing
                $index = array_search($row->person_schedule_id, $newScheduleIds);
                unset($newScheduleIds[$index]);
            } else {
                $row->delete();
            }
        }
        if (!$this->getConnection()->getPdo()->inTransaction()) {
            throw new StorageException(_('Not in transaction!'));
        }
        foreach ($newScheduleIds as $id) {
            $query = $this->getTable()->where('person_schedule_id', $id)->where('payment.state !=? OR payment.state IS NULL', ModelPayment::STATE_CANCELED);
            $count = $query->count();
            if ($count > 0) {
                /** @var ModelSchedulePayment $model */
                $model = $query->fetch();
                throw new DuplicatePaymentException(sprintf(
                    _('Item "%s" has already another payment.'),
                    $model->getPersonSchedule()->getLabel()
                ));
            }
            $this->createNewModel(['payment_id' => $payment->payment_id, 'person_schedule_id' => $id]);
        }
    }

    /**
     * @param ArrayHash $data
     * @return int
     */
    private function prepareData($data): array {
        $data = (array)json_decode($data);
        return array_keys(array_filter($data, function ($value) {
            return $value;
        }));
    }
}
