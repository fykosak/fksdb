<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelRole extends AbstractModelSingle {

    public const CONTESTANT = 'contestant';
    public const ORG = 'org';
    public const REGISTERED = 'registered';
    public const GUEST = 'guest';
}
