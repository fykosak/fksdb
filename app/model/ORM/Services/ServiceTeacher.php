<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceTeacher extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_TEACHER;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelTeacher';
}
