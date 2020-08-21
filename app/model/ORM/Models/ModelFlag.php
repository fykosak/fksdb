<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DeprecatedLazyModel;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @property-read int fid
 */
class ModelFlag extends AbstractModelSingle {
    use DeprecatedLazyModel;
}
