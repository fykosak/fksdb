<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DeprecatedLazyModel;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @property-read string fid
 * @property-read int flag_id
 */
class ModelFlag extends AbstractModelSingle {
    use DeprecatedLazyModel;
}
