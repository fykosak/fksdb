<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\DI\Container;

/**
 * @phpstan-template THolder of ModelHolder
 */
trait OrganizerMailTrait
{
    private Container $container;

    public function injectContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * @param THolder $holder
     */
    protected function getMessageLog(ModelHolder $holder): TestLogger
    {
        $logger = new TestLogger();
        foreach (DataTestFactory::getTeamTests($this->container) as $test) {
            $test->run($logger, $holder->getModel());
        }
        return $logger;
    }
}
