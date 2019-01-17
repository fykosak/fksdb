<?php

namespace FKSDB\Transitions\Conditions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\ModelEvent;
use FKSDB\Transitions\IStateModel;

class ExplicitEventRole extends EventRole {
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var string
     */
    private $resource;

    public function __construct(EventAuthorizator $eventAuthorizator, string $privilege, ModelEvent $event, string $resource) {
        parent::__construct($eventAuthorizator, $privilege);
        $this->event = $event;
        $this->resource = $resource;
    }

    protected function evaluate(IStateModel $model = null): bool {
        return $this->eventAuthorizator->isAllowed($this->resource, $this->privilege, $this->event);
    }
}
