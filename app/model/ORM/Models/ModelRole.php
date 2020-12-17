<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\DeprecatedLazyModel;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelRole extends AbstractModelSingle {
    use DeprecatedLazyModel;

    public const CONTESTANT = 'contestant';
    public const ORG = 'org';
    public const REGISTERED = 'registered';
    public const GUEST = 'guest';
}
