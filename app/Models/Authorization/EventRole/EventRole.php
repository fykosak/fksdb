<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\Authorization\Grant;
use FKSDB\Models\ORM\Models\ModelEvent;

abstract class EventRole extends Grant
{
    protected ModelEvent $event;

    public function __construct(string $roleId, ModelEvent $event)
    {
        parent::__construct($roleId, $event->getContest());
        $this->event = $event;
    }

    final public function getEvent(): ModelEvent
    {
        return $this->event;
    }
}
