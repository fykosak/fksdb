<?php

namespace ORM\Services\Events;

use AbstractServiceSingle;
use DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceDsefGroup extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_DSEF_GROUP;
    protected $modelClassName = 'ORM\Models\Events\ModelDsefGroup';

}

