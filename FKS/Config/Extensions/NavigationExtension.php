<?php

namespace FKS\Config\Extensions;

use Nette\Config\CompilerExtension;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class NavigationExtension extends CompilerExtension {

    public function loadConfiguration() {
        parent::loadConfiguration();

        $builder = $this->getContainerBuilder();
        $config = $this->getConfig(array(
            'nodes' => array(),
            'structure' => array(),
        ));
        $navbar = $builder->addDefinition($this->prefix('navbar'))
                        ->setClass('FKS\Components\Controls\Navbar')
                        ->setShared(FALSE)->setAutowired(FALSE);
        foreach ($config['nodes'] as $nodeId => $arguments) {
            $navbar->addSetup('$service->createNode(?, ?);', array($nodeId, $arguments));
        }
    }

}
