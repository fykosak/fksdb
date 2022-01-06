<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment;

use FKSDB\Models\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Models\Payment\SymbolGenerator\Generators\DefaultGenerator;
use FKSDB\Models\Payment\Transition\PaymentMachine;
use Nette\DI\CompilerExtension;

class PaymentExtension extends CompilerExtension {
    public const MACHINE_PREFIX = 'machine.';

    public function loadConfiguration(): void {
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
                ->addSetup('setTransitions', [$transitionsGenerator])
                ->addSetup('setScheduleGroupTypes', [$item['scheduleGroupType']]);
        }
    }
}
