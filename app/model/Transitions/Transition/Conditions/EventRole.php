<?php


namespace FKSDB\Transitions\Conditions;


use Authorization\EventAuthorizator;
use FKSDB\Transitions\Statement;

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
