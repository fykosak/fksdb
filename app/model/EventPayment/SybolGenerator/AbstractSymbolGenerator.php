<?php

namespace FKSDB\EventPayment\SymbolGenerator;

use FKSDB\ORM\ModelEventPayment;

abstract class AbstractSymbolGenerator {
    /**
     * @var \ServiceEventPayment;
     */
    protected $serviceEventPayment;

    public function __construct(\ServiceEventPayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    abstract public function crate(ModelEventPayment $modelEventPayment);
}
