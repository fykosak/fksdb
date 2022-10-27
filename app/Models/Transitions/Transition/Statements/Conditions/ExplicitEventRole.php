<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class ExplicitEventRole extends EventRole
{

    private EventModel $event;

    private string $resource;

    public function __construct(
        EventAuthorizator $eventAuthorizator,
        string $privilege,
        EventModel $event,
        string $resource
    ) {
        parent::__construct($privilege, $eventAuthorizator);
        $this->event = $event;
        $this->resource = $resource;
    }

    public function __invoke(ModelHolder $holder): bool
    {
        return $this->eventAuthorizator->isAllowed($this->resource, $this->privilege, $this->event);
    }
}
