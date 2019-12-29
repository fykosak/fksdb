<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Payment\SymbolGenerator\Generators\DefaultGenerator;
use Nette\Config\CompilerExtension;
use Tracy\Debugger;

/**
 * Class PaymentExtension
 * @package FKSDB\Config\Extensions
 */
class PaymentExtension extends CompilerExtension {

    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        Debugger::barDump($this->config);
        foreach ($this->config as $item) {
            $builder->addDefinition($this->prefix('symbolGenerator.' . $item['eventId']))
                ->setFactory(DefaultGenerator::class)->addSetup('setUp', [
                    $item['symbolGenerator']['variableSymbolStart'],
                    $item['symbolGenerator']['variableSymbolEnd'],
                    $item['symbolGenerator']['info'],
                ]);
        }
    }
}

