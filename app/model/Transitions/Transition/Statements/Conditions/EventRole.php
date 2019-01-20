<?php


namespace FKSDB\Transitions\Statements\Conditions;


use Authorization\EventAuthorizator;
use FKSDB\Transitions\Statements\Statement;

abstract class EventRole extends Statement {
    /**
     * @var EventAuthorizator
     */
    protected $eventAuthorizator;
    /**
     * @var string
     */
    protected $privilege;

    public function __construct(EventAuthorizator $eventAuthorizator, string $privilege) {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->privilege = $privilege;
    }
}
