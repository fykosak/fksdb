<?php

namespace FKSDB\Components\Forms\Containers\Models;

use Nette\Forms\Container;

/**
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContainerWithOptions extends Container {
    /**
     * ContainerWithOptions constructor.
     * @param \Nette\DI\Container|null $container
     */
    public function __construct(\Nette\DI\Container $container = null) {
        if ($container) {
            $container->callInjects($this);
        }
        parent::__construct();
    }

    use OptionsTrait;
}
