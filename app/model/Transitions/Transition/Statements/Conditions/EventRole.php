<?php

namespace FKSDB\Transitions\Statements\Conditions;

use Authorization\EventAuthorizator;
use FKSDB\Transitions\Statements\Statement;

/**
 * Class EventRole
 * @package FKSDB\Transitions\Statements\Conditions
 */
abstract class EventRole extends Statement {
    /**
     * @var EventAuthorizator
     */
    protected $eventAuthorizator;
    /**
     * @var string
     */
    protected $privilege;

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
