<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Payment\SymbolGenerator\Generators\Generators\DefaultGenerator;
use FKSDB\Payment\Transition\PaymentMachine;
use Nette\Config\CompilerExtension;

/**
 * Class PaymentExtension
 * @package FKSDB\Config\Extensions
 */
class PaymentExtension extends CompilerExtension {
    const MACHINE_PREFIX = 'machine.';

    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        foreach ($this->config as $item) {
            $symbolGenerator = $builder->addDefinition($this->prefix('symbolGenerator.' . $item['eventId']))
                ->setFactory(DefaultGenerator::class)->addSetup('setUp', [
                    $item['symbolGenerator']['variableSymbolStart'],
                    $item['symbolGenerator']['variableSymbolEnd'],
                    $item['symbolGenerator']['info'],
                ]);
            $priceCalculator = $builder->addDefinition($this->prefix('priceCalculator.' . $item['eventId']))
                ->setFactory(PriceCalculator::class);
            foreach ($item['priceCalculator']['preProcess'] as $preProcess) {
                $priceCalculator->addSetup('addPreprocess', [
                    new $preProcess(),
                ]);
            }
            $transitionsGenerator = $builder->addDefinition($this->prefix('transitionsGenerator.' . $item['eventId']))
                ->setFactory($item['transitionsGenerator']);

            $builder->addDefinition($this->prefix(self::MACHINE_PREFIX . $item['eventId']))
                ->setFactory(PaymentMachine::class)
                ->addSetup('setEventId', [$item['eventId']])
                ->addSetup('setPriceCalculator', [$priceCalculator])
                ->addSetup('setSymbolGenerator', [$symbolGenerator])
                ->addSetup('setTransitions', [$transitionsGenerator]);

        }
    }
}

