<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodation\Boolean;
use FKSDB\Components\Forms\Controls\PersonAccommodation\Matrix;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiHotels;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiNights;
use Nette\Forms\Controls\TextInput;
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

    private function createMatrixSelect($eventId) {
        return new Matrix($this->serviceEventAccommodation, $eventId);
    }

    private function createMultiHotelsSelect($eventId) {
        return new MultiHotels($this->serviceEventAccommodation, $eventId);
    }

    private function createMultiNightsSelect($eventId) {
        return new MultiNights($this->serviceEventAccommodation, $eventId);
    }

    private function createBooleanSelect($eventId) {
        return new Boolean($this->serviceEventAccommodation, $eventId);
    }

    /**
     * @param string $fieldName
     * @param integer $eventId
     * @return TextInput
     */
    public function createField($fieldName, $eventId): TextInput {
        switch ($fieldName) {
            case Matrix::RESOLUTION_ID:
                return $this->createMatrixSelect($eventId);
            case self::RESOLUTION_AUTO:
                return $this->autoResolution($eventId);
            case MultiNights::RESOLUTION_ID:
                return $this->createMultiNightsSelect($eventId);
            case MultiHotels::RESOLUTION_ID:
                return $this->createMultiHotelsSelect($eventId);
            case Boolean::RESOLUTION_ID:
                return $this->createBooleanSelect($eventId);
            default:
                throw new InvalidArgumentException();
        }
    }

    private function autoResolution($eventId) {
        $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($eventId);
        throw  new NotImplementedException('Mode auto is not implemtn');
        return null;
    }
}
