<?php

use ORM\CachingServiceTrait;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContest extends AbstractServiceSingle {

    use CachingServiceTrait;

    protected $tableName = DbNames::TAB_CONTEST;
    protected $modelClassName = 'ModelContest';

}

