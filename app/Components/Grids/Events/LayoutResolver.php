<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class LayoutResolver {

    use SmartObject;

    public const TEMPLATE_EXT = '.latte';

    private array $definitions;

    private string $templateDir;

    /**
     * LayoutResolver constructor.
     * @param string $templateDir
     * @param array $definitions
     */
    public function __construct(string $templateDir, array $definitions) {
        $this->templateDir = $templateDir;
        $this->definitions = $definitions;
    }

    /**
     * @param ModelEvent $event
     * @return string
     * @throws NotImplementedException
     */
    public function getFormLayout(ModelEvent $event): string {
        return $this->getTemplate($event, 'formLayout');
    }

    /**
     * @param ModelEvent $event
     * @param $type
     * @return string
     * @throws NotImplementedException
     */
    private function getTemplate(ModelEvent $event, string $type): string {
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
        throw new NotImplementedException();
    }

}
