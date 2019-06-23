<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\Components\React\IReactComponent;
use FKSDB\Components\React\ReactField;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventAccommodation;
use Nette\Forms\Controls\TextInput;

/**
 * Class AccommodationField
 * @package FKSDB\Components\Forms\Controls\PersonAccommodation
 */
abstract class AccommodationField extends TextInput implements IReactComponent {

    use ReactField;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * AccommodationField constructor.
     * @param ModelEvent $event
     */
    public function __construct(ModelEvent $event) {
        parent::__construct(_('Accommodation'));
        $this->event = $event;
        $this->appendProperty();
        $this->registerMonitor();
    }

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'accommodation';
    }

    /**
     * @return string
     */
    public function getModuleName(): string {
        return 'events';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getData(): string {
        $accommodations = $this->event->getEventAccommodationsAsArray();

        $accommodationDef = [];
        foreach ($accommodations as $accommodation) {
            $model = ModelEventAccommodation::createFromActiveRow($accommodation);
            $accommodationDef[] = $model->__toArray();
        }
        return json_encode($accommodationDef);
    }

    /**
     * @param $obj
     */
    public function attached($obj) {
        parent::attached($obj);
        $this->attachedReact($obj);
    }

    /**
     * @return array
     */
    public function getActions(): array {
        return [];
    }
}
