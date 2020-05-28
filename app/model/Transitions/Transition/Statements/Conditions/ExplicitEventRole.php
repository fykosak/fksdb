<?php

namespace FKSDB\Transitions\Statements\Conditions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\Models\ModelEvent;

/**
 * Class ExplicitEventRole
 * *
 */
class ExplicitEventRole extends EventRole {
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var string
     */
    private $resource;

    /**
     * ExplicitEventRole constructor.
     * @param EventAuthorizator $eventAuthorizator
     * @param string $privilege
     * @param ModelEvent $event
     * @param string $resource
     */
    public function __construct(EventAuthorizator $eventAuthorizator, string $privilege, ModelEvent $event, string $resource) {
        parent::__construct($eventAuthorizator, $privilege);
        $this->event = $event;
        $this->resource = $resource;
    }

    /**
     * @param array $args
     * @return bool
     */
    protected function evaluate(...$args): bool {
        return $this->eventAuthorizator->isContestOrgAllowed($this->resource, $this->privilege, $this->event);
    }
}
