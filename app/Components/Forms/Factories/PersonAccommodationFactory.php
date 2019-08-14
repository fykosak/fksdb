<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodation\AccommodationField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MatrixField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiHotelsField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\MultiNightsField;
use FKSDB\Components\Forms\Controls\PersonAccommodation\SingleField;
use FKSDB\ORM\Models\ModelEvent;
use Nette\NotImplementedException;

/**
 * Class PersonAccommodationFactory
 * @package FKSDB\Components\Forms\Factories
 */
class PersonAccommodationFactory {
    /**
     * @var string;
     */
    const RESOLUTION_AUTO = 'RESOLUTION_AUTO';

    /**
     * @param ModelEvent $event
     * @return MatrixField
     */
    private function createMatrixSelect(ModelEvent $event): MatrixField {
        return new MatrixField($event);
    }

    /**
     * @param ModelEvent $event
     * @return MultiHotelsField
     */
    private function createMultiHotelsSelect(ModelEvent $event): MultiHotelsField {
        return new MultiHotelsField($event);
    }

    /**
     * @param ModelEvent $event
     * @return MultiNightsField
     */
    private function createMultiNightsSelect(ModelEvent $event): MultiNightsField {
        return new MultiNightsField($event);
    }

    /**
     * @param ModelEvent $event
     * @return SingleField
     */
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
            case MultiNightsField::RESOLUTION_ID:
                return $this->createMultiNightsSelect($event);
            case MultiHotelsField::RESOLUTION_ID:
                return $this->createMultiHotelsSelect($event);
            case SingleField::RESOLUTION_ID:
                return $this->createBooleanSelect($event);
            case self::RESOLUTION_AUTO:
            default:
                throw new NotImplementedException(\sprintf(_('Mode %s is not implement'), $fieldName), 501);
        }
    }

    /**
     * @param $eventId
     * @return null
     */
    private function autoResolution($eventId) {
        // $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($eventId);
        return null;
    }
}
