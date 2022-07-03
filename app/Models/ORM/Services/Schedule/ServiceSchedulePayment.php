<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Schedule;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Models\Schedule\ModelSchedulePayment;
use Fykosak\NetteORM\Service;
use FKSDB\Models\Payment\Handler\DuplicatePaymentException;
use FKSDB\Models\Payment\Handler\EmptyDataException;
use FKSDB\Models\Submits\StorageException;

class ServiceSchedulePayment extends Service
{

    /**
     * @throws DuplicatePaymentException
     * @throws EmptyDataException
     * @throws NotImplementedException
     * @throws StorageException
     * @throws ModelException
     */
    public function storeItems(array $data, ModelPayment $payment): void
    {
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            throw new StorageException(_('Not in transaction!'));
        }

        $newScheduleIds = $this->filerData($data);
        if (count($newScheduleIds) == 0) {
            throw new EmptyDataException(_('No item selected.'));
        }
        $payment->related(DbNames::TAB_SCHEDULE_PAYMENT)->delete();
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

    private function filerData(array $data): array
    {
        $results = [];
        foreach ($data as $values) {
            foreach ($values as $id => $value) {
                if ($value) {
                    $results[] = $id;
                }
            }
        }
        return $results;
    }
}
