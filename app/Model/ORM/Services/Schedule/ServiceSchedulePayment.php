<?php

namespace FKSDB\Model\ORM\Services\Schedule;

use FKSDB\Model\Exceptions\NotImplementedException;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\DeprecatedLazyDBTrait;
use FKSDB\Model\ORM\Models\ModelPayment;
use FKSDB\Model\ORM\Models\Schedule\ModelSchedulePayment;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use FKSDB\Model\Payment\Handler\DuplicatePaymentException;
use FKSDB\Model\Payment\Handler\EmptyDataException;
use FKSDB\Model\Submits\StorageException;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceSchedulePayment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceSchedulePayment extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_SCHEDULE_PAYMENT, ModelSchedulePayment::class);
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
