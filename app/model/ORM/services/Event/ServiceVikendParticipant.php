<?php

namespace ORM\Services\Events;

use AbstractServiceSingle;
use DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceVikendParticipant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_VIKEND_PARTICIPANT;
    protected $modelClassName = 'ORM\Models\Events\ModelVikendParticipant';

}

