<?php

declare(strict_types=1);

namespace FKSDB\Models\Pipeline;

use Fykosak\Utils\Logging\MemoryLogger;
use Nette\DI\Container;

abstract class Stage
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->container->callInjects($this);
    }

    /**
     * @param MemoryLogger $logger
     * @param mixed $data
     * @return mixed
     */
    abstract public function __invoke(MemoryLogger $logger, $data);
}
