<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodation\AccommodationField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MatrixField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiHotelsField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiNightsField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\SingleField;
use Nette\InvalidArgumentException;
use Nette\NotImplementedException;
use ServiceEventAccommodation;

class PersonAccommodationFactory {
    /**
     * @var string;
     */
    const RESOLUTION_AUTO = 'RESOLUTION_AUTO';
    /**
     * @var ServiceEventAccommodation
     */
    private $serviceEventAccommodation;

    public function __construct(ServiceEventAccommodation $serviceEventAccommodation) {
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    private function createMatrixSelect($eventId): MatrixField {
        return new MatrixField($this->serviceEventAccommodation, $eventId);
    }

    private function createMultiHotelsSelect($eventId): MultiHotelsField {
        return new MultiHotelsField($this->serviceEventAccommodation, $eventId);
    }

    private function createMultiNightsSelect($eventId): MultiNightsField {
        return new MultiNightsField($this->serviceEventAccommodation, $eventId);
    }

    private function createBooleanSelect($eventId): SingleField {
        return new SingleField($this->serviceEventAccommodation, $eventId);
    }

    /**
     * @param string $fieldName
     * @param integer $eventId
     * @return AccommodationField
     */
    public function createField($fieldName, $eventId): AccommodationField {
        switch ($fieldName) {
            case MatrixField::RESOLUTION_ID:
                return $this->createMatrixSelect($eventId);
            case self::RESOLUTION_AUTO:
                throw  new NotImplementedException('Mode auto is not implement');
                break;
            case MultiNightsField::RESOLUTION_ID:
                return $this->createMultiNightsSelect($eventId);
            case MultiHotelsField::RESOLUTION_ID:
                return $this->createMultiHotelsSelect($eventId);
            case SingleField::RESOLUTION_ID:
                return $this->createBooleanSelect($eventId);
            default:
                throw new InvalidArgumentException();
        }
    }

    private function autoResolution($eventId) {
        // $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($eventId);
        return null;
    }
}
