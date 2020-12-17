<?php

namespace FKSDB\Model\Transitions\Transition\Statements\Conditions;

use FKSDB\Model\Authorization\EventAuthorizator;
use FKSDB\Model\ORM\Models\ModelEvent;

/**
 * Class ExplicitEventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ExplicitEventRole extends EventRole {

    private ModelEvent $event;

    private string $resource;

    public function __construct(EventAuthorizator $eventAuthorizator, string $privilege, ModelEvent $event, string $resource) {
        parent::__construct($eventAuthorizator, $privilege);
        $this->event = $event;
        $this->resource = $resource;
    }

    protected function evaluate(...$args): bool {
        return $this->eventAuthorizator->isContestOrgAllowed($this->resource, $this->privilege, $this->event);
    }
}