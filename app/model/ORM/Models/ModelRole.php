<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelRole extends AbstractModelSingle {

    const CONTESTANT = 'contestant';
    const ORG = 'org';
    const REGISTERED = 'registered';
    const GUEST = 'guest';

}
