<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceTaskContribution extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_TASK_CONTRIBUTION;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelTaskContribution';

}
