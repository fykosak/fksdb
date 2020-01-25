<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\Controls\Stalking\StalkingService;
use Nette\Config\CompilerExtension;

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
        }
        $builder->addDefinition($this->prefix('stalking'))
            ->setFactory(StalkingService::class)
            ->addSetup('setSections', [$config]);
    }
}
