<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodation\Matrix;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;

class PersonAccommodationFactory {
    /**
     * @var \ServiceEventAccommodation
     */
    private $serviceEventAccommodation;

    public function __construct(\ServiceEventAccommodation $serviceEventAccommodation) {
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    public function createMatrixSelect($eventId) {
        $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($eventId);

        $accommodationDef = [];
        /**
         * @var $accommodation \ModelEventAccommodation
         */
        foreach ($accommodations as $accommodation) {
            $accommodationDef[] = $accommodation->__toArray();
        }
        $control = new Matrix();
        $control->setAccommodationDefinition($accommodationDef);
        return $control;

    }

    /**
     * @param string $fieldName
     * @param integer $eventId
     * @return BaseControl
     */
    public function createField($fieldName, $eventId) {
        switch ($fieldName) {
            case Matrix::ResolutionId:
                return $this->createMatrixSelect($eventId);
            default:
                throw new InvalidArgumentException();
        }
    }
}
