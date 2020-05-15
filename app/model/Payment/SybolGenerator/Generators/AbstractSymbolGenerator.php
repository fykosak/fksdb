<?php

namespace FKSDB\Payment\SymbolGenerator\Generators;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;

/**
 * Class AbstractSymbolGenerator
 * @package FKSDB\Payment\SymbolGenerator
 */
abstract class AbstractSymbolGenerator {
    /**
     * @var ServicePayment;
     */
    protected $servicePayment;

    /**
     * AbstractSymbolGenerator constructor.
     * @param ServicePayment $servicePayment
     */
    public function __construct(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param ModelPayment $modelPayment
     * @return array
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    abstract protected function create(ModelPayment $modelPayment);

    /**
     * @param ModelPayment $modelPayment
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function __invoke(ModelPayment $modelPayment) {
        $info = $this->create($modelPayment);
        $modelPayment->update($info);
    }
}
