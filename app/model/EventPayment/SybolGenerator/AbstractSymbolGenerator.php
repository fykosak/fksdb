<?php

namespace FKSDB\EventPayment\SymbolGenerator;

use FKSDB\ORM\ModelPayment;

abstract class AbstractSymbolGenerator {
    /**
     * @var \ServicePayment;
     */
    protected $serviceEventPayment;

    public function __construct(\ServicePayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    /**
     * @param ModelPayment $modelEventPayment
     * @return mixed
     * @throws AlreadyGeneratedSymbolsException
     */
    abstract public function create(ModelPayment $modelEventPayment);
}
