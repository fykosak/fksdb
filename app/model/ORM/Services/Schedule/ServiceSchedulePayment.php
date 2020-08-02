<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\Schedule\ModelSchedulePayment;
use FKSDB\Payment\Handler\DuplicatePaymentException;
use FKSDB\Submits\StorageException;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceSchedulePayment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceSchedulePayment extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    /**
     * ServiceSchedulePayment constructor.
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_SCHEDULE_PAYMENT, ModelSchedulePayment::class);
    }

    /**
     * @param string $data
     * @param ModelPayment $payment
     * @return void
     * @throws DuplicatePaymentException
     * @throws NotImplementedException
     */
    public function prepareAndUpdate(string $data, ModelPayment $payment) {
        if (!$this->getConnection()->getPdo()->inTransaction()) {
            throw new StorageException(_('Not in transaction!'));
        }

        $oldRows = $this->getTable()->where('payment_id', $payment->payment_id);

        $newScheduleIds = $this->prepareData($data);
        /* if (count($newScheduleIds) == 0) {
             throw new EmptyDataException(_('Nebola vybraná žiadá položka'));
         };*/
        /** @var ModelSchedulePayment $row */
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

    private function prepareData(string $data): array {
        $data = (array)json_decode($data);
        return array_keys(array_filter($data, function ($value) {
            return $value;
        }));
    }
}
