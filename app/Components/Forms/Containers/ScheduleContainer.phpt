<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;

/**
 * Class ScheduleContainer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ScheduleContainer extends ContainerWithOptions {
    /**
     * @var bool
     */
    private $isAttached = false;

    /**
     * ScheduleContainer constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->monitor(IContainer::class, function () {
            if (!$this->isAttached) {
                $this->configure();
                $this->isAttached = true;
            }
        });
    }

    /**
     * @return void
     */
    protected function configure() {

    }
}
