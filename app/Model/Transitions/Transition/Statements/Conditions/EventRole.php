<?php

namespace FKSDB\Model\Transitions\Transition\Statements\Conditions;

use FKSDB\Model\Authorization\EventAuthorizator;
use FKSDB\Model\Transitions\Transition\Statements\Statement;

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
