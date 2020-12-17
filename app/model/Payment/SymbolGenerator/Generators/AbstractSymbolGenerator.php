<?php

namespace FKSDB\Payment\SymbolGenerator\Generators;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;
use FKSDB\Transitions\Callbacks\ITransitionCallback;
use FKSDB\Transitions\IStateModel;

/**
 * Class AbstractSymbolGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractSymbolGenerator implements ITransitionCallback {

    protected ServicePayment $servicePayment;

    public function __construct(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param ModelPayment $modelPayment
     * @param $args
     * @return array
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    abstract protected function create(ModelPayment $modelPayment, ...$args): array;

    /**
     * @param IStateModel $modelPayment
     * @param $args
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function __invoke(IStateModel $modelPayment, ...$args): void {
        $info = $this->create($modelPayment, ...$args);
        $this->servicePayment->updateModel2($modelPayment, $info);
    }

    /**
     * @param IStateModel $modelPayment
     * @param $args
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function invoke(IStateModel $modelPayment, ...$args): void {
        $info = $this->create($modelPayment, ...$args);
        $this->servicePayment->updateModel2($modelPayment, $info);
    }
}
