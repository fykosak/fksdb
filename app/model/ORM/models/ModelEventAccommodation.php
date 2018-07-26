<?php

use Nette\DateTime;

/**
 * Class ModelEventAccommodation
 * @package ORM\Models\Events
 * @property integer event_accommodation_id
 * @property integer event_id
 * @property integer capacity
 * @property string name
 * @property integer address_id
 * @property integer price_kc
 * @property integer price_eur
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

    public function __toArray() {
        return [
            'eventAccommodationId' => $this->event_accommodation_id,
            'eventId' => $this->event_id,
            'capacity' => $this->capacity,
            'usedCapacity' => $this->getUsedCapacity(),
            'name' => $this->name,
            'addressId' => $this->address_id,
            'price' => [
                'kc' => $this->price_kc,
                'eur' => $this->price_eur,
            ],
            'date' => $this->date->__toString(),
        ];
    }
}
