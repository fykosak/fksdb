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

        $builder->addDefinition($this->prefix('stalking'))
            ->setFactory(StalkingService::class)
            ->addSetup('setSections', [$this->config['components']]);
    }
}
