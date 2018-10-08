<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\Components\React\IReactComponent;
use FKSDB\Components\React\ReactField;
use Nette\Forms\Controls\TextInput;

abstract class AccommodationComponent extends TextInput implements IReactComponent {

    use ReactField;
    /**
     * @var \ServiceEventAccommodation
     */
    private $serviceEventAccommodation;
    /**
     * @var integer
     */
    private $eventId;

    public function __construct(\ServiceEventAccommodation $serviceEventAccommodation, $eventId) {
        parent::__construct();
        $this->serviceEventAccommodation = $serviceEventAccommodation;
        $this->eventId = $eventId;
        $this->appendProperty();
    }

    public function getComponentName() {
        return 'accommodation';
    }

    public function getModuleName() {
        return 'events';
    }

    /**
     * @return string
     */
    public function getData() {
        $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($this->eventId);

        $accommodationDef = [];
        /**
         * @var $accommodation \FKSDB\ORM\ModelEventAccommodation
         */
        foreach ($accommodations as $accommodation) {
            $accommodationDef[] = $accommodation->__toArray();
        }
        return count($accommodationDef) ? json_encode($accommodationDef) : NULL;
    }
}
