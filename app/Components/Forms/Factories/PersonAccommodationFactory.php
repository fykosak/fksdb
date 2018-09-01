<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodationMatrix;

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
        $this->serviceEventAccommodation;
        $control = new PersonAccommodationMatrix();
        $control->setAccommodationDefinition($accommodationDef);
        return $control;

    }
}
