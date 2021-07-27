<?php

namespace FKSDB\Models\Router;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;

class RouterExtension extends CompilerExtension {

    public function loadConfiguration(): void {
        parent::loadConfiguration();

        $container = $this->getContainerBuilder();
        $config = $this->getConfig();
        /** @var ServiceDefinition $router */
        $router = $container->getDefinition('router');

        foreach ($config['routes'] as $action) {
            $mask = $action['mask'];
            unset($action['mask']);
            $flagsBin = 0;
            if (isset($action['flags'])) {
                $flags = $action['flags'];
                if (!is_array($flags)) {
                    $flags = [$flags];
                }
                foreach ($flags as $flag) {
                    $binFlag = constant("Nette\Application\Routers\Route::$flag");
                    $flagsBin |= $binFlag;
                }
                unset($action['flags']);
            }
            $router->addSetup('addRoute', [$mask, $action, $flagsBin]);
        }
    }
}
