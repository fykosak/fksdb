<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestYear extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_CONTEST_YEAR;
    protected $modelClassName = 'FKSDB\ORM\ModelContestYear';

}

