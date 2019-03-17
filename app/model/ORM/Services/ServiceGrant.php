<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGrant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_ROLE;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelGrant';

}

