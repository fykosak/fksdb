<?php

namespace FKSDB\Payment\SymbolGenerator;

use FKSDB\ORM\ModelPayment;

abstract class AbstractSymbolGenerator {
    /**
     * @var \ServicePayment;
     */
    protected $servicePayment;

    public function __construct(\ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return mixed
     * @throws AlreadyGeneratedSymbolsException
     */
    abstract public function create(ModelPayment $modelPayment);
}
