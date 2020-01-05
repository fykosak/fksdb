<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\Controls\Stalking\StalkingService;
use FKSDB\Components\DatabaseReflection\Links\Link;
use Nette\Application\BadRequestException;
use Nette\Config\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

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
            $config[$tableName]['links'] = $this->prepareLinks($tableName, $component);
        }
        $builder->addDefinition($this->prefix('stalking'))
            ->setFactory(StalkingService::class)
            ->addSetup('setSections', [$config]);
    }

    /**
     * @param string $tableName
     * @param array $component
     * @return ServiceDefinition[]
     * @throws BadRequestException
     */
    private function prepareLinks(string $tableName, $component): array {
        $linkFactories = [];
        if (isset($component['links'])) {
            foreach ($component['links'] as $index => $link) {
                $linkFactories[] = $this->prepareLinkFactory($tableName, $index, $link);
            }
        }
        return $linkFactories;
    }

    /**
     * @param $tableName
     * @param $linkAccessKey
     * @param $def
     * @return ServiceDefinition
     * @throws BadRequestException
     */
    private function prepareLinkFactory(string $tableName, $linkAccessKey, $def): ServiceDefinition {
        $builder = $this->getContainerBuilder();
        if (is_array($def)) {
            return $builder->addDefinition($this->prefix($linkAccessKey . '.' . $tableName))
                ->setFactory(Link::class)
                ->addSetup('setParams', [$def['destination'], $def['params'], $def['title']]);
        }
        if (is_string($def)) {
            return $builder->addDefinition($this->prefix($linkAccessKey . '.' . $tableName))
                ->setFactory($def);
        }
        throw new BadRequestException();
    }
}
