<?php

namespace FKSDB\Transitions\Statements\Conditions;

use FKSDB\Authorization\EventAuthorizator;
use FKSDB\Transitions\Statements\Statement;

/**
 * Class EventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class EventRole extends Statement {

    protected EventAuthorizator $eventAuthorizator;

    protected string $privilege;

    /**
     * EventRole constructor.
     * @param EventAuthorizator $eventAuthorizator
     * @param string $privilege
     */
    public function __construct(EventAuthorizator $eventAuthorizator, string $privilege) {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->privilege = $privilege;
    }
}
