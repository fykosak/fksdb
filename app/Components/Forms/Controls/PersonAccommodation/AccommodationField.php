<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\Components\React\IReactComponent;
use FKSDB\Components\React\ReactField;
use FKSDB\ORM\ModelEventAccommodation;
use Nette\Forms\Controls\TextInput;

abstract class AccommodationField extends TextInput implements IReactComponent {

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
        $this->registerMonitor();
    }

    public function getComponentName(): string {
        return 'accommodation';
    }

    public function getModuleName(): string {
        return 'events';
    }

    /**
     * @return string
     */
    public function getData(): string {
        $accommodations = $this->serviceEventAccommodation->getAccommodationForEvent($this->eventId);

        $accommodationDef = [];
        foreach ($accommodations as $accommodation) {
            $model = ModelEventAccommodation::createFromTableRow($accommodation);
            $accommodationDef[] = $model->__toArray();
        }
        return json_encode($accommodationDef);
    }

    public function attached($obj) {
        parent::attached($obj);
        $this->attachedReact($obj);
    }
}
