<?php

class ServiceEventAccommodation extends \AbstractServiceSingle {

    protected $tableName = DbNames::TAB_EVENT_ACCOMMODATION;
    protected $modelClassName = 'ModelEventAccommodation';

    public function getAccommodationFroEvent($eventId) {
        return $this->getTable()->where('event_id', $eventId);
    }
}
