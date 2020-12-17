<?php

namespace FKSDB\Model\ORM\Models;

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