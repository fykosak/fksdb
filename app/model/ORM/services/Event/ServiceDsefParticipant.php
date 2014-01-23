<?php

namespace ORM\Services\Events;

use AbstractServiceSingle;
use DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceDsefParticipant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_DSEF_PARTICIPANT;
    protected $modelClassName = 'ORM\Models\Events\ModelDsefParticipant';

}

