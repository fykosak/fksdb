<?php

class ServiceEventPersonAccommodation extends \AbstractServiceSingle {
    protected $tableName = DbNames::TAB_EVENT_PERSON_ACCOMMODATION;
    protected $modelClassName = 'ModelEventPersonAccommodation';

    public function getAllAccommodationForPersonAndEvent($personId, $event_id) {
        return $this->getTable()->where('person_id', $personId)->where('event_accommodation.event_id', $event_id);
    }
}
