<?php

namespace FKSDB\Model\ORM\Models;

use FKSDB\ORM\DeprecatedLazyModel;


/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int contest_id
 * @property-read int ac_year
 * @property-read int year
 */
class ModelContestYear extends AbstractModelSingle {
    use DeprecatedLazyModel;
}