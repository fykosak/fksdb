<?php

namespace FKSDB\Payment\SymbolGenerator;

use FKSDB\ORM\Models\ModelPayment;

/**
 * Class AbstractSymbolGenerator
 * @package FKSDB\Payment\SymbolGenerator
 */
abstract class AbstractSymbolGenerator {
    /**
     * @var \ServicePayment;
     */
    protected $servicePayment;

    /**
     * AbstractSymbolGenerator constructor.
     * @param \ServicePayment $servicePayment
     */
    public function __construct(\ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelPayment $modelPayment
     * @return mixed
     * @throws AlreadyGeneratedSymbolsException
     */
    abstract public function create(ModelPayment $modelPayment);
}
