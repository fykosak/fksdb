<?php

use FKSDB\ORM\ModelPayment;
use Nette\ArrayHash;

class ServiceEventPersonAccommodation extends \AbstractServiceSingle {
    protected $tableName = DbNames::TAB_EVENT_PERSON_ACCOMMODATION;
    protected $modelClassName = 'FKSDB\ORM\ModelEventPersonAccommodation';

}
