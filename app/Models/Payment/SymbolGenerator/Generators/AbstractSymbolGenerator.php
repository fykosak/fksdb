<?php

namespace FKSDB\Models\Payment\SymbolGenerator\Generators;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use FKSDB\Models\Payment\SymbolGenerator\AlreadyGeneratedSymbolsException;

/**
 * Class AbstractSymbolGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractSymbolGenerator implements TransitionCallback {

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
     * @param ModelHolder $holder
     * @param $args
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function __invoke(ModelHolder $holder, ...$args): void {
        /** @var ModelPayment $model */
        $model = $holder->getModel();
        $info = $this->create($model, ...$args);
        $this->servicePayment->updateModel2($model, $info);
    }

    /**
     * @param ModelHolder $holder
     * @param $args
     * @throws AlreadyGeneratedSymbolsException
     * @throws UnsupportedCurrencyException
     */
    final public function invoke(ModelHolder $holder, ...$args): void {
        /** @var ModelPayment $model */
        $model = $holder->getModel();
        $info = $this->create($model, ...$args);
        $this->servicePayment->updateModel2($model, $info);
    }
}
