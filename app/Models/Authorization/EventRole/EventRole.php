<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\Security\Role;

abstract class EventRole implements Role
{
    protected EventModel $event;
    private string $roleId;

    public function __construct(string $roleId, EventModel $event)
    {
        $this->event = $event;
        $this->roleId = $roleId;
    }

    final public function getEvent(): EventModel
    {
        return $this->event;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }
}
