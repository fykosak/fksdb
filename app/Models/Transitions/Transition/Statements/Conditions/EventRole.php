<?php

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\Transitions\Transition\Statements\Statement;

abstract class EventRole extends Statement {

    protected EventAuthorizator $eventAuthorizator;
    protected ?string $privilege;

    /**
     * EventRole constructor.
     * @param EventAuthorizator $eventAuthorizator
     * @param string|null $privilege
     */
    public function __construct(EventAuthorizator $eventAuthorizator, ?string $privilege) {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->privilege = $privilege;
    }
}
