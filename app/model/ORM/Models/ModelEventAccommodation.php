<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;
use Nette\Utils\DateTime;

/**
 * Class FKSDB\ORM\Models\ModelEventAccommodation
 * @package ORM\Models\Events
 * @property-read integer event_accommodation_id
 * @property-read integer event_id
 * @property-read integer capacity
 * @property-read string name
 * @property-read integer address_id
 * @property-read integer price_kc
 * @property-read integer price_eur
 * @property-read DateTime date
 * @property-read ActiveRow address
 * @property-read ActiveRow event
 */
class ModelEventAccommodation extends AbstractModelSingle implements IResource, IEventReferencedModel {
    const ACC_DATE_FORMAT = 'Y-m-d';

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'event.accommodation';
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromActiveRow($this->event);
    }

    /**
     * @return ModelAddress|null
     */
    public function getAddress() {
        if ($this->address) {
            return ModelAddress::createFromActiveRow($this->address);
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
     * @return \Nette\Database\Table\GroupedSelection
     */
    public function getAccommodated() {
        return $this->related(DbNames::TAB_EVENT_PERSON_ACCOMMODATION);
    }

    /**
     * @return int
     */
    public function getUsedCapacity(): int {
        return $this->related(DbNames::TAB_EVENT_PERSON_ACCOMMODATION)->count();
    }

    /**
     * @return array
     * @throws \Exception
     */
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
            'label' => $this->__toString(),
            'date' => $this->date->format(self::ACC_DATE_FORMAT),
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLabel(): string {
        $date = clone $this->date;
        $fromDate = $date->format('d. m.');
        $toDate = $date->add(new \DateInterval('P1D'))->format('d. m. Y');
        return \sprintf(_('Ubytovaní od %s do %s v hoteli %s.'), $fromDate, $toDate, $this->name);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function __toString(): string {
        return $this->getLabel();
    }
}
