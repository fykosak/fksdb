<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStudyYear extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STUDY_YEAR;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelStudyYear';

}
