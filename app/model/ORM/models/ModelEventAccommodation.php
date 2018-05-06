<?php

namespace ORM\Models\Events;

use Nette\DateTime;

/**
 * Class ModelEventAccommodation
 * @package ORM\Models\Events
 * @property integer event_accommodation_id
 * @property integer event_id
 * @property integer capacity
 * @property string name
 * @property integer address_id
 * @property integer price
 * @property DateTime date,
 */
class ModelEventAccommodation extends \AbstractModelSingle {
    /**
     * @return \ModelEvent
     */
    public function getEvent() {
        $data = $this->event;
        return \ModelEvent::createFromTableRow($data);
    }

    /**
     * @return \ModelAddress
     */
    public function getAddress() {
        $data = $this->address;
        return \ModelAddress::createFromTableRow($data);
    }

    /**
     * @return integer
     */
    public function getAvailableCapacity() {
        return ($this->getCapacity() - $this->getUsedCapacity());
    }

    /**
     * @return integer
     */
    public function getCapacity() {
        return $this->capacity;
    }

    /**
     * @return integer
     */
    public function getUsedCapacity() {
        return $this->related(\DbNames::TAB_EVENT_PERSON_ACCOMMODATION)->count();
    }
}
