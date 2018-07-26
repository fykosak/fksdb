<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodationMatrix;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;

class PersonAccommodationFactory {
    /**
     * @var \ServiceEventAccommodation
     */
    private $serviceEventAccommodation;

    public function __construct(\ServiceEventAccommodation $serviceEventAccommodation) {
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    public function createMatrixSelect($options) {
        Debugger::barDump($options);
        $accommodations = $this->serviceEventAccommodation->getAccommodationFroEvent($options['event_id']);
        $accommodationDef = [];
        /**
         * @var $accommodation \ModelEventAccommodation
         */
        foreach ($accommodations as $accommodation) {
            $accommodationDef[] = $accommodation->__toArray();
        }
        Debugger::barDump($accommodationDef);
        $this->serviceEventAccommodation;
        $control = new PersonAccommodationMatrix();
        $control->setAccommodationDefinition($accommodationDef);
        //$control->setAttribute('data-accommodation-def', Json::encode($accommodationDef));
        return $control;

    }
}
