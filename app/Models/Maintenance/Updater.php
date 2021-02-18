<?php

namespace FKSDB\Models\Maintenance;

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

    public function installBranch(string $requestedBranch): void {
        $deployment = $this->container->getParameters()['updater']['deployment'];
        foreach ($deployment as $path => $branch) {
            if ($branch != $requestedBranch) {
                continue;
            }
            $this->install($path, $branch);
        }
    }

    private function install(string $path, string $branch): void {
        $user = $this->container->getParameters()['updater']['installUser'];
        $script = $this->container->getParameters()['updater']['installScript'];
        $cmd = "sudo -u {$user} {$script} $path $branch >/dev/null 2>/dev/null &";
        Debugger::log("Running: $cmd");
        shell_exec($cmd);
    }

}
