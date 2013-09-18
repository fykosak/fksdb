<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceEventParticipant extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_PARTICIPANT;
    protected $modelClassName = 'ModelEventParticipant';

}

