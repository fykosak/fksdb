<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\Controls\Navigation\NavigationFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class NavigationExtension extends CompilerExtension {

    private array $createdNodes = [];

    public function loadConfiguration() {
        parent::loadConfiguration();

        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();
        $navbar = $builder->addDefinition('navbar')
            ->setClass(NavigationFactory::class);
        $navbar->setAutowired(true);


        foreach ($config['nodes'] as $nodeId => $arguments) {
            $this->createNode($navbar, $nodeId, $arguments);
        }

        $this->createFromStructure($config['structure'], $navbar);


        $navbar->addSetup('$service->setStructure(?);', [$config['structure']]);
    }

    /**
     * @param ServiceDefinition $navbar
     * @param int|string $nodeId
     * @param array $arguments
     */
    private function createNode(ServiceDefinition $navbar, $nodeId, $arguments = []) {
        if (!isset($arguments['link'])) {
            $this->parseIdAsLink($nodeId, $arguments);
        }
        $this->createdNodes[$nodeId] = 1;
        $arguments['nodeId'] = $nodeId;
        $navbar->addSetup('$service->createNode(?, ?);', [$nodeId, $arguments]);
    }

    /**
     * @param iterable $structure
     * @param ServiceDefinition $navbar
     * @param null $parent
     */
    private function createFromStructure(iterable $structure, ServiceDefinition $navbar, $parent = null) {
        foreach ($structure as $nodeId => $children) {
            if (is_array($children)) {
                if (!isset($this->createdNodes[$nodeId])) {
                    $this->createNode($navbar, $nodeId);
                    if ($parent) {
                        $navbar->addSetup('$service->addParent(?, ?);', [$nodeId, $parent]);
                    }
                }
                $this->createFromStructure($children, $navbar, $nodeId);
            } else {
                $nodeId = $children;
                if (!isset($this->createdNodes[$nodeId])) {
                    $this->createNode($navbar, $nodeId);
                    if ($parent) {
                        $navbar->addSetup('$service->addParent(?, ?);', [$nodeId, $parent]);
                    }
                }
            }
        }
    }

    /**
     * @param int|string $nodeId
     * @param array $arguments
     * @return void
     */
    private function parseIdAsLink($nodeId, &$arguments) {
        $fullQualityAction = str_replace('.', ':', $nodeId);
        $a = strrpos($fullQualityAction, ':');
        $presenterName = substr($fullQualityAction, 0, $a);
        $action = substr($fullQualityAction, $a + 1);
        $arguments['linkPresenter'] = $presenterName;
        $arguments['linkAction'] = $action;
        $arguments['linkParams'] = isset($arguments['params']) ? $arguments['params'] : null;
        unset($arguments['params']);
    }

}
