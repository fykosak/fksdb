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

    /**
     * @param ModelEventPayment $modelEventPayment
     * @return mixed
     * @throws AlreadyGeneratedSymbols
     */
    abstract public function create(ModelEventPayment $modelEventPayment);
}
