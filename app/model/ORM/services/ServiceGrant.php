<?php

namespace FKSDB\ORM\Services;

use AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGrant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_ROLE;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelGrant';

}

