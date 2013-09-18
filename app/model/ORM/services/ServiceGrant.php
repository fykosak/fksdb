<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGrant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_ROLE;
    protected $modelClassName = 'ModelGrant';

}

