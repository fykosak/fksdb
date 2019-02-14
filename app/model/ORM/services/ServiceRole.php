<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRole extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_ROLE;
    protected $modelClassName = 'FKSDB\ORM\ModelRole';

}

