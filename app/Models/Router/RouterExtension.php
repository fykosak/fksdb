<?php

namespace FKSDB\Models\Router;

use Nette\DI\CompilerExtension;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RouterExtension extends CompilerExtension {

    public function loadConfiguration(): void {
        parent::loadConfiguration();

        $container = $this->getContainerBuilder();
        $config = $this->getConfig();
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
            $router->addSetup('$service[] = new Nette\Application\Routers\Route(?, ?, ?);', [$mask, $action, $flagsBin]);
        }
    }
}
