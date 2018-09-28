<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodation\Matrix;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;
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

    private function createSingleAccommodationSelect($eventId) {
        return null;
    }
    private function createSingleDaySelect($eventId) {
        return null;
    }
    private function createBooleanSelect($eventId) {
        return null;
    }

    /**
     * @param string $fieldName
     * @param integer $eventId
     * @return BaseControl
     */
    public function createField($fieldName, $eventId) {
        switch ($fieldName) {
            case Matrix::RESOLUTION_ID:
                return $this->createMatrixSelect($eventId);
            case self::RESOLUTION_AUTO:
                return $this->autoResolution($eventId);
            default:
                throw new InvalidArgumentException();
        }
    }

    private function autoResolution($eventId) {
        $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($eventId);

        return null;
    }
}
