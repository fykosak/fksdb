<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodation\AccommodationField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\Matrix;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiHotels;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiNights;
use FKSDB\Components\Forms\Controls\PersonAccommodation\Single as BooleanField;
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

    private function createMatrixSelect($eventId): Matrix {
        return new Matrix($this->serviceEventAccommodation, $eventId);
    }

    private function createMultiHotelsSelect($eventId): MultiHotels {
        return new MultiHotels($this->serviceEventAccommodation, $eventId);
    }

    private function createMultiNightsSelect($eventId): MultiNights {
        return new MultiNights($this->serviceEventAccommodation, $eventId);
    }

    private function createBooleanSelect($eventId): BooleanField {
        return new BooleanField($this->serviceEventAccommodation, $eventId);
    }

    /**
     * @param string $fieldName
     * @param integer $eventId
     * @return AccommodationField
     */
    public function createField($fieldName, $eventId): AccommodationField {
        switch ($fieldName) {
            case Matrix::RESOLUTION_ID:
                return $this->createMatrixSelect($eventId);
            case self::RESOLUTION_AUTO:
                throw  new NotImplementedException('Mode auto is not implemtn');
                break;
            case MultiNights::RESOLUTION_ID:
                return $this->createMultiNightsSelect($eventId);
            case MultiHotels::RESOLUTION_ID:
                return $this->createMultiHotelsSelect($eventId);
            case BooleanField::RESOLUTION_ID:
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
