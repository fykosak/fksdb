<?php

/**
 * Class ServiceEventPersonAccommodation
 */
class ServiceEventPersonAccommodation extends \AbstractServiceSingle {
    protected $tableName = DbNames::TAB_EVENT_PERSON_ACCOMMODATION;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelEventPersonAccommodation';

}
