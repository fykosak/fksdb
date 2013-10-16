<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQuery extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY;
    protected $modelClassName = 'ModelStoredQuery';

}

