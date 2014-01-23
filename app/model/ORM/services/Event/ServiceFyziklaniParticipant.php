<?php

namespace ORM\Services\Events;

use AbstractServiceSingle;
use DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceFyziklaniParticipant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_FYZIKLANI_PARTICIPANT;
    protected $modelClassName = 'ORM\Models\Events\ModelFyziklaniParticipant';

}

