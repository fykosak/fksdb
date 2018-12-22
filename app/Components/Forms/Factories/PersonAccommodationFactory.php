<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodation\AccommodationField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MatrixField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiHotelsField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiNightsField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\SingleField;
use FKSDB\ORM\ModelEvent;
use Nette\InvalidArgumentException;
use Nette\NotImplementedException;

class PersonAccommodationFactory {
    /**
     * @var string;
     */
    const RESOLUTION_AUTO = 'RESOLUTION_AUTO';

    private function createMatrixSelect(ModelEvent $event): MatrixField {
        return new MatrixField($event);
    }

    private function createMultiHotelsSelect(ModelEvent $event): MultiHotelsField {
        return new MultiHotelsField($event);
    }

    private function createMultiNightsSelect(ModelEvent $event): MultiNightsField {
        return new MultiNightsField($event);
    }

    private function createBooleanSelect(ModelEvent $event): SingleField {
        return new SingleField($event);
    }

    /**
     * @param string $fieldName
     * @param ModelEvent $event
     * @return AccommodationField
     */
    public function createField($fieldName, ModelEvent $event): AccommodationField {
        switch ($fieldName) {
            case MatrixField::RESOLUTION_ID:
                return $this->createMatrixSelect($event);
            case self::RESOLUTION_AUTO:
                throw  new NotImplementedException('Mode auto is not implement');
                break;
            case MultiNightsField::RESOLUTION_ID:
                return $this->createMultiNightsSelect($event);
            case MultiHotelsField::RESOLUTION_ID:
                return $this->createMultiHotelsSelect($event);
            case SingleField::RESOLUTION_ID:
                return $this->createBooleanSelect($event);
            default:
                throw new InvalidArgumentException();
        }
    }

    private function autoResolution($eventId) {
        // $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($eventId);
        return null;
    }
}
