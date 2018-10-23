<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventType extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_TYPE;
    protected $modelClassName = 'FKSDB\ORM\ModelEventType';

}

