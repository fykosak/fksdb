<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\Schedule\ModelSchedulePayment;
use FKSDB\Payment\Handler\DuplicatePaymentException;
use FKSDB\Payment\Handler\EmptyDataException;
use FKSDB\Submits\StorageException;

/**
 * Class ServiceSchedulePayment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceSchedulePayment extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    protected function getTableName(): string {
        return DbNames::TAB_SCHEDULE_PAYMENT;
    }

    public function getModelClassName(): string {
        return ModelSchedulePayment::class;
    }

    /**
     * @param array $data
     * @param ModelPayment $payment
     * @return void
     * @throws DuplicatePaymentException
     * @throws NotImplementedException
     * @throws EmptyDataException
     */
    public function store(array $data, ModelPayment $payment): void {
        if (!$this->getConnection()->getPdo()->inTransaction()) {
            throw new StorageException(_('Not in transaction!'));
        }

        $this->getTable()->where('payment_id', $payment->payment_id)->delete();

        $newScheduleIds = $this->filerData($data);
        if (count($newScheduleIds) == 0) {
            throw new EmptyDataException(_('Nebola vybraná žiadá položka'));
        }
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
