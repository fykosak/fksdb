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
    private $definitions;

    /**
     * @var string
     */
    private $templateDir;

    function __construct($templateDir, $definitions) {
        $this->templateDir = $templateDir;
        $this->definitions = $definitions;
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
        $result = null;
        foreach ($this->definitions as $definition) {
            $yearCond = ($definition['years'] === true) || in_array($eventYear, $definition['years']);
            $eventTypeCond = ($definition['eventTypes'] == $eventTypeId) || in_array($eventTypeId, $definition['eventTypes']);
            if ($yearCond && $eventTypeCond) {
                $result = $definition[$type];
                break;
            }
        }
        if ($result) {
            return $this->templateDir . DIRECTORY_SEPARATOR . $result . self::TEMPLATE_EXT;
        }
    }

}
