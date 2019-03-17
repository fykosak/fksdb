<?php

namespace FKSDB\ORM\Services;

use AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRole extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_ROLE;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelRole';

}

