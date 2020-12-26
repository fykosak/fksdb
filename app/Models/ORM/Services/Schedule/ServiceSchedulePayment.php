<?php

namespace FKSDB\Models\ORM\Services\Schedule;

use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\DeprecatedLazyDBTrait;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Models\Schedule\ModelSchedulePayment;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\Payment\Handler\DuplicatePaymentException;
use FKSDB\Models\Payment\Handler\EmptyDataException;
use FKSDB\Models\Submits\StorageException;

/**
 * Class ServiceSchedulePayment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceSchedulePayment extends AbstractServiceSingle {

    use DeprecatedLazyDBTrait;

    /**
     * @param array $data
     * @param ModelPayment $payment
     * @return void
     * @throws DuplicatePaymentException
     * @throws EmptyDataException
     * @throws NotImplementedException
     * @throws StorageException
     * @throws ModelException
     */
    public function store(array $data, ModelPayment $payment): void {
        if (!$this->getConnection()->getPdo()->inTransaction()) {
            throw new StorageException(_('Not in transaction!'));
        }

        $newScheduleIds = $this->filerData($data);
        if (count($newScheduleIds) == 0) {
            throw new EmptyDataException(_('Nebola vybraná žiadá položka'));
        }

        $this->getTable()->where('payment_id', $payment->payment_id)->delete();
        foreach ($newScheduleIds as $id) {
            /** @var ModelSchedulePayment $model */
            $model = $this->getTable()->where('person_schedule_id', $id)
                ->where('payment.state !=? OR payment.state IS NULL', ModelPayment::STATE_CANCELED)
                ->fetch();
            if ($model) {
                throw new DuplicatePaymentException(sprintf(
                    _('Item "%s" has already another payment.'),
                    $model->getPersonSchedule()->getLabel()
                ));
            }
            $this->createNewModel(['payment_id' => $payment->payment_id, 'person_schedule_id' => $id]);
        }
    }

    private function filerData(array $data): array {
        $results = [];
        foreach ($data as $person => $values) {
            foreach ($values as $id => $value) {
                if ($value) {
                    $results[] = $id;
                }
            }
        }
        return $results;
    }
}
