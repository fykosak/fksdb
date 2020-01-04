<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\Controls\Stalking\StalkingService;
use FKSDB\Components\DatabaseReflection\Links\Link;
use Nette\Application\BadRequestException;
use Nette\Config\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;
use Tracy\Debugger;

/**
 * Class StalkingExtension
 * @package FKSDB\Config\Extensions
 */
class StalkingExtension extends CompilerExtension {
    /**
     * @throws BadRequestException
     */
    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        $config = [];
        foreach ($this->config['components'] as $tableName => $component) {
            $config[$tableName] = $component;
            if (isset($component['detailLink'])) {
                $config[$tableName]['detailLink'] = $this->prepareLinkFactory($builder, $tableName, 'detailLink', $component['detailLink']);
            }
            if (isset($component['editLink'])) {
                $config[$tableName]['editLink'] = $this->prepareLinkFactory($builder, $tableName, 'editLink', $component['editLink']);
            }
        }
        $builder->addDefinition($this->prefix('stalking'))
            ->setFactory(StalkingService::class)
            ->addSetup('setSections', [$config]);
    }

    /**
     * @param ContainerBuilder $builder
     * @param $tableName
     * @param $linkAccessKey
     * @param $def
     * @return ServiceDefinition
     * @throws BadRequestException
     */
    private function prepareLinkFactory(ContainerBuilder $builder, $tableName, $linkAccessKey, $def): ServiceDefinition {

        if (is_array($def)) {
            return $builder->addDefinition($this->prefix('stalking.' . $linkAccessKey . '.' . $tableName))
                ->setFactory(Link::class)
                ->addSetup('setParams', [$def['destination'], $def['params'], $def['title']]);
        }
        if (is_string($def)) {
            return $builder->addDefinition($this->prefix('stalking.' . $linkAccessKey . '.' . $tableName))
                ->setFactory($def);
        }
        throw new BadRequestException();
    }
}
