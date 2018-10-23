<?php

namespace FKSDB\ORM;

use DbNames;
use Nette\Database\Table\ActiveRow;
use Nette\DateTime;
use Nette\Security\IResource;

/**
 * Class FKSDB\ORM\ModelEventAccommodation
 * @package ORM\Models\Events
 * @property integer event_accommodation_id
 * @property integer event_id
 * @property integer capacity
 * @property string name
 * @property integer address_id
 * @property integer price_kc
 * @property integer price_eur
 * @property DateTime date
 * @property ActiveRow address
 * @property ActiveRow event
 */
class ModelEventAccommodation extends \AbstractModelSingle implements IResource {
    const ACC_DATE_FORMAT = 'Y-m-d';

    public function getResourceId(): string {
        return 'eventAccommodation';
    }

    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
    }

    /**
     * @return ModelAddress
     */
    public function getAddress() {
        if ($this->address) {
            return ModelAddress::createFromTableRow($this->address);
        }
        return null;
    }

    /**
     * @return integer
     */
    public function getAvailableCapacity(): int {
        return ($this->getCapacity() - $this->getUsedCapacity());
    }

    /**
     * @return integer
     */
    public function getCapacity(): int {
        return $this->capacity;
    }

    /**
     * @return integer
     */
    public function getUsedCapacity(): int {
        return $this->related(DbNames::TAB_EVENT_PERSON_ACCOMMODATION)->count();
    }

    public function __toArray(): array {
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
            'date' => $this->date->format(self::ACC_DATE_FORMAT),
        ];
    }
}
