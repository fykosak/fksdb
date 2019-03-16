<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePostContact extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_POST_CONTACT;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelPostContact';

}

