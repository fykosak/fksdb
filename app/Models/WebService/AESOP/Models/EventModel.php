<?php

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Nette\DI\Container;

abstract class EventModel extends AESOPModel {

    protected string $eventName;
    protected ServiceEvent $serviceEvent;

    public function __construct(Container $container, ModelContestYear $contestYear, string $eventName) {
        parent::__construct($container, $contestYear);
        $this->eventName = $eventName;
    }

    public function injectServiceEvent(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    protected function mapEventNameToTypeId(): int {
        $idMapping = [
            'klani' => 1,
            'dsef' => 2,
            'vaf' => 3,
            'sous.j' => 4,
            'sous.p' => 5,
            'tsaf' => 7,
            'fol' => 9,
            'tabor' => 10,
            'setkani.j' => 11,
            'setkani.p' => 12,
            'dsef2' => 14,
            'fov' => 16,
        ];
        return $idMapping[$this->eventName] ?? 0;
    }
}
