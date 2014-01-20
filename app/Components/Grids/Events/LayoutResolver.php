<?php

namespace FKSDB\Components\Grids\Events;

use ModelEvent;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class LayoutResolver extends Object {

    const TEMPLATE_EXT = '.latte';

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $templateDir;

    function __construct($templateDir, $data) {
        $this->templateDir = $templateDir;
        $this->data = $data;
    }

    public function getTableLayout(ModelEvent $event) {
        return $this->getTemplate($event, 'tableLayout');
    }

    public function getFormLayout(ModelEvent $event) {
        return $this->getTemplate($event, 'formLayout');
    }

    private function getTemplate(ModelEvent $event, $type) {
        $eventTypeId = $event->event_type_id;
        $eventYear = $event->event_year;
        $definitions = $this->data[$eventTypeId];
        $result = null;
        foreach ($definitions as $definition) {
            if ($definition['years'] === true) {
                $result = $definition[$type];
            }
            if (is_array($definition['years']) && in_array($eventYear, $definition['years'])) {
                $result = $definition[$type];
                break;
            }
        }
        if ($result) {
            return $this->templateDir . DIRECTORY_SEPARATOR . $result . self::TEMPLATE_EXT;
        }
    }

}
