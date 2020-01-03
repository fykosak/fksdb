<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\Controls\Stalking\StalkingService;
use Nette\Config\CompilerExtension;
use Tracy\Debugger;

/**
 * Class StalkingExtension
 * @package FKSDB\Config\Extensions
 */
class StalkingExtension extends CompilerExtension {

    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        $config = [];
        foreach ($this->config['components'] as $tableName => $component) {
            $config[$tableName] = $component;
            if (isset($component['detailLink'])) {
                $factory = $builder->addDefinition($this->prefix('stalking.detailLink.' . $tableName))
                    ->setFactory($component['detailLink']);
                $config[$tableName]['detailLink'] = $factory;
            }
            if (isset($component['editLink'])) {
                $factory = $builder->addDefinition($this->prefix('stalking.editLink' . $tableName))
                    ->setFactory($component['editLink']);
                $config[$tableName]['editLink'] = $factory;
            }
        }
        Debugger::barDump($this->config['components']);
        $builder->addDefinition($this->prefix('stalking'))
            ->setFactory(StalkingService::class)
            ->addSetup('setSections', [$config]);
    }
}
