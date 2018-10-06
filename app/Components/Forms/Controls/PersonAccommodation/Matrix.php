<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\Components\React\IReactComponent;
use FKSDB\Components\React\ReactField;
use Nette\Forms\Controls\TextInput;

class Matrix extends TextInput implements IReactComponent {
    const RESOLUTION_ID = 'matrix';

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
        parent::__construct(_('Accommodation'));
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

    public function getMode() {
        return self::RESOLUTION_ID;
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
