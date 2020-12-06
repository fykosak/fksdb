<?php

namespace FKSDB\Transitions\Transition\Statements\Conditions;

use FKSDB\Authorization\EventAuthorizator;
use FKSDB\Transitions\Transition\Statements\Statement;

/**
 * Class EventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class EventRole extends Statement {

    protected EventAuthorizator $eventAuthorizator;

    protected string $privilege;

    public function __construct(EventAuthorizator $eventAuthorizator, string $privilege) {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->privilege = $privilege;
    }
}
