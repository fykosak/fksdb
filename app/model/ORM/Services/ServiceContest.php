<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContest extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_CONTEST;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelContest';

}

