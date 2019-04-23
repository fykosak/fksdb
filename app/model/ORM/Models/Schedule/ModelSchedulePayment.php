<?php

namespace FKSDB\ORM\Models\Schedule;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Database\Table\ActiveRow;

/**
 * Class ModelSchedulePayment
 * @package FKSDB\ORM\Models\Schedule
 * @property-readActiveRow payment
 * @property-readActiveRow person_schedule
 */
class ModelSchedulePayment extends AbstractModelSingle {
    /**
     * @return ModelPayment
     */
    public function getPayment(): ModelPayment {
        return ModelPayment::createFromTableRow($this->payment);
    }

    /**
     * @return ModelPersonSchedule
     */
    public function getPersonSchedule(): ModelPersonSchedule {
        return ModelPersonSchedule::createFromTableRow($this->person_schedule);
    }
}
