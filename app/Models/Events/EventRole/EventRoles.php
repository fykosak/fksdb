<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\EventRole;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\SmartObject;

class EventRoles
{
    use SmartObject;

    public ModelPerson $person;
    public ModelEvent $event;

    private array $roles = [];

    public function __construct(ModelPerson $person, ModelEvent $event)
    {
        $this->person = $person;
        $this->event = $event;
    }

    public function addRole(EventRole $role): void
    {
        $this->roles[] = $role;
    }

    public function hasRoles(): bool
    {
        return (bool)count($this->roles);
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
