<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceStoredQueryParameter extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY_PARAM;
    protected $modelClassName = 'ModelStoredQueryParameter';

}

