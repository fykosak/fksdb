<?php

namespace FKSDB\ORM\Services\Events;

use AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceFyziklaniParticipant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_FYZIKLANI_PARTICIPANT;
    protected $modelClassName = 'FKSDB\ORM\Models\Events\ModelFyziklaniParticipant';

}

