<?php

namespace FKSDB\Payment\SymbolGenerator\Generators;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;

/**
 * Class AbstractSymbolGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractSymbolGenerator {

    protected ServicePayment $servicePayment;

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
    abstract protected function create(ModelPayment $modelPayment): array;

    /**
     * @param ModelPayment $modelPayment
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function __invoke(ModelPayment $modelPayment): void {
        $info = $this->create($modelPayment);
        $this->servicePayment->updateModel2($modelPayment,$info);
    }
}
