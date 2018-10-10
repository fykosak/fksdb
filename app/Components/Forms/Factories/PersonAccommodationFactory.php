<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodation\Matrix;
use FKSDB\Components\Forms\TableReflection\TableReflectionFactory;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;
use ServiceEventAccommodation;

class PersonAccommodationFactory extends TableReflectionFactory {
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

    public function createField(string $fieldName, array $data = []): BaseControl {
        switch ($fieldName) {
            case Matrix::RESOLUTION_ID:
                return $this->createMatrixSelect($data['eventId']);
            case self::RESOLUTION_AUTO:
                return $this->autoResolution($data['eventId']);
            default:
                throw new InvalidArgumentException();
        }
    }

    private function autoResolution($eventId) {
        $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($eventId);

        return null;
    }
}
