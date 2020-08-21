<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DeprecatedLazyModel;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int contest_id
 */
class ModelGrant extends AbstractModelSingle {
    use DeprecatedLazyModel;
}
