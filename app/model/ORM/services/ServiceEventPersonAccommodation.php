<?php

use FKSDB\Messages\Message;

class ServiceEventPersonAccommodation extends \AbstractServiceSingle {
    protected $tableName = DbNames::TAB_EVENT_PERSON_ACCOMMODATION;
    protected $modelClassName = 'ModelEventPersonAccommodation';

}
