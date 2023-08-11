<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\Transitions\Statement;

/**
 * @template ArgType
 * @phpstan-implements Statement<bool,ArgType>
 */
abstract class EventRole implements Statement
{
    protected EventAuthorizator $eventAuthorizator;
    protected ?string $privilege;

    public function __construct(string $privilege, EventAuthorizator $eventAuthorizator)
    {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->privilege = $privilege;
    }
}
