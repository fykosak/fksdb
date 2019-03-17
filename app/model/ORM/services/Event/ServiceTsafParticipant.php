<?php

namespace FKSDB\ORM\Services\Events;

use AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTsafParticipant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_TSAF_PARTICIPANT;
    protected $modelClassName = 'FKSDB\ORM\Models\Events\ModelTsafParticipant';

}

