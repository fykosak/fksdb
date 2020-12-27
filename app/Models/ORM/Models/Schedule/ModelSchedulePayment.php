<?php

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPayment;
use Nette\Database\Table\ActiveRow;

/**
 * Class ModelSchedulePayment
 * @property-read ActiveRow payment
 * @property-read ActiveRow person_schedule
 * @property-read int person_schedule_id
 */
class ModelSchedulePayment extends AbstractModelSingle {

    public function getPayment(): ModelPayment {
        return ModelPayment::createFromActiveRow($this->payment);
    }

    public function getPersonSchedule(): ModelPersonSchedule {
        return ModelPersonSchedule::createFromActiveRow($this->person_schedule);
    }
}
