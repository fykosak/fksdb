<?php

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\ORM\Models\ModelEvent;

class ExplicitEventRole extends EventRole {

    private ModelEvent $event;

    private string $resource;

    public function __construct(EventAuthorizator $eventAuthorizator, string $privilege, ModelEvent $event, string $resource) {
        parent::__construct($eventAuthorizator, $privilege);
        $this->event = $event;
        $this->resource = $resource;
    }

    protected function evaluate(...$args): bool {
        return $this->eventAuthorizator->isAllowed($this->resource, $this->privilege, $this->event);
    }
}
