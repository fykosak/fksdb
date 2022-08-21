<?php

declare(strict_types=1);

namespace FKSDB\Models\Pipeline;

use Fykosak\Utils\Logging\MemoryLogger;

abstract class Stage
{
    /**
     * @param MemoryLogger $logger
     * @param mixed $data
     * @return mixed
     */
    abstract public function __invoke(MemoryLogger $logger, $data);
}
