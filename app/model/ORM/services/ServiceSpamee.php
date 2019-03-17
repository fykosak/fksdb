<?php

namespace FKSDB\ORM\Services;

use AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @deprecated
 */
class ServiceSpamee extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_SPAMEE;
    protected $modelClassName = 'ModelSpamee';

}

