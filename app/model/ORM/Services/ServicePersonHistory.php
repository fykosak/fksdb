<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePersonHistory extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON_HISTORY;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelPersonHistory';

}

