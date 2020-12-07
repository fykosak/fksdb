<?php

namespace FKSDB\Model\Payment\SymbolGenerator\Generators;

use FKSDB\Model\ORM\Models\ModelPayment;
use FKSDB\Model\ORM\Services\ServicePayment;
use FKSDB\Model\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Model\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;

/**
 * Class AbstractSymbolGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractSymbolGenerator {

    protected ServicePayment $servicePayment;

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
        $modelPayment->update($info);
    }
}
