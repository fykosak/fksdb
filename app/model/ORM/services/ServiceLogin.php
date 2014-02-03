<?php

use ORM\CachingServiceTrait;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceLogin extends AbstractServiceSingle {

    use CachingServiceTrait;

    protected $tableName = DbNames::TAB_LOGIN;
    protected $modelClassName = 'ModelLogin';

}

