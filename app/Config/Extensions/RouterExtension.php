<?php

namespace FKSDB\Config\Extensions;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RouterExtension extends CompilerExtension {

    public function loadConfiguration() {
        parent::loadConfiguration();

        $container = $this->getContainerBuilder();
        $config = $this->getConfig([
            'routes' => [],
            'disableSecured' => false,
        ]);

        $router = $container->getDefinition('router');
        $disableSecured = $config['disableSecured'];

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
                    if ($disableSecured && $binFlag === Route::SECURED) {
                        continue;
                    }
                    $flagsBin |= $binFlag;
                }
                unset($action['flags']);
            }

            $router->addSetup('$service[] = new Nette\Application\Routers\Route(?, ?, ?);', [$mask, $action, $flagsBin]);
        }
    }
}
