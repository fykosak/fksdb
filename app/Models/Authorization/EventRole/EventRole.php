<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Security\Role;

abstract class EventRole implements Role
{
    protected ModelEvent $event;
    private string $roleId;

    public function __construct(string $roleId, ModelEvent $event)
    {
        $this->event = $event;
        $this->roleId = $roleId;
    }

    final public function getEvent(): ModelEvent
    {
        return $this->event;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }
}
