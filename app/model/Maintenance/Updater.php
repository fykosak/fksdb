<?php

namespace FKSDB\Maintenance;

use Nette\DI\Container;
use Nette\SmartObject;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Updater {
    use SmartObject;

    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param string $requestedBranch
     * @return void
     */
    public function installBranch($requestedBranch): void {
        $deployment = $this->container->getParameters()['updater']['deployment'];
        foreach ($deployment as $path => $branch) {
            if ($branch != $requestedBranch) {
                continue;
            }
            $this->install($path, $branch);
        }
    }

    /**
     * @param mixed $path
     * @param mixed $branch
     */
    private function install($path, $branch) {
        $user = $this->container->getParameters()['updater']['installUser'];
        $script = $this->container->getParameters()['updater']['installScript'];
        $cmd = "sudo -u {$user} {$script} $path $branch >/dev/null 2>/dev/null &";
        Debugger::log("Running: $cmd");
        shell_exec($cmd);
    }

}
