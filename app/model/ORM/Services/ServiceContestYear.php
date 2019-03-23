<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestYear extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_CONTEST_YEAR;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelContestYear';

}

