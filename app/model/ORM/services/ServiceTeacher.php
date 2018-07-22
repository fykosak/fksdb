<?php

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceTeacher extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_TEACHER;
    protected $modelClassName = 'ModelTeacher';
}
