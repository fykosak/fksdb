<?php

namespace FKSDB\ORM\Models\Schedule;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Database\Table\ActiveRow;

/**
 * Class ModelSchedulePayment
 * @package FKSDB\ORM\Models\Schedule
 * @property-read ActiveRow payment
 * @property-read ActiveRow person_schedule
 */
class ModelSchedulePayment extends AbstractModelSingle {
    /**
     * @return ModelPayment
     */
    public function getPayment(): ModelPayment {
        return ModelPayment::createFromActiveRow($this->payment);
    }

    /**
     * @return ModelPersonSchedule
     */
    public function getPersonSchedule(): ModelPersonSchedule {
        return ModelPersonSchedule::createFromActiveRow($this->person_schedule);
    }
}
