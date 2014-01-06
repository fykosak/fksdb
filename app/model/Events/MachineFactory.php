<?php

namespace Events;

use ModelEventType;
use Nette\DI\IContainer;
use SystemContainer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class MachineFactory {

    /**
     * @var SystemContainer
     */
    private $container;

    function __construct(IContainer $container) {
        $this->container = $container;
    }

    /**
     * @param ModelEventType $eventType
     * @param int $eventYear
     * @return Machine
     */
    public function createMachine(ModelEventType $eventType, $eventYear) {
        
        return null;
        
    }

    private function getFilename(ModelEventType $eventType) {
        return $this->machineDir . DIRECTORY_SEPARATOR . $this->machineFilenames[$eventType->getPrimary()] . '.neon';
    }

}
