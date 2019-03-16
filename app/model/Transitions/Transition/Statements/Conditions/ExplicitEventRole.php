<?php

namespace FKSDB\Transitions\Statements\Conditions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\Transitions\IStateModel;

/**
 * Class ExplicitEventRole
 * @package FKSDB\Transitions\Statements\Conditions
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
     * @param IStateModel|null $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        return $this->eventAuthorizator->isAllowed($this->resource, $this->privilege, $this->event);
    }
}
