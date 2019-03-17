<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\DbNames;

/**
 * Class FKSDB\ORM\Services\ServiceEventPersonAccommodation
 * @deprecated
 */
class ServiceEventPersonAccommodation extends \FKSDB\ORM\AbstractServiceSingle {
    protected $tableName = DbNames::TAB_EVENT_PERSON_ACCOMMODATION;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelEventPersonAccommodation';

}
