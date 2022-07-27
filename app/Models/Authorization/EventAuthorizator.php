<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Authorization\EventRole\EventRole;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelLogin;
use Nette\Security\Resource;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\SmartObject;

class EventAuthorizator
{
    use SmartObject;

    private User $user;
    private Permission $permission;
    private ContestAuthorizator $contestAuthorizator;

    public function __construct(User $user, Permission $acl, ContestAuthorizator $contestAuthorizator)
    {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $user;
        $this->permission = $acl;
    }

    /**
     * @param Resource|string|null $resource
     */
    public function isAllowed($resource, ?string $privilege, ModelEvent $event): bool
    {
        if ($this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest())) {
            return true;
        }
        foreach ($this->getRolesForEvent($event) as $role) {
            if ($this->permission->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return EventRole[]
     */
    private function getRolesForEvent(ModelEvent $event): array
    {
        $login = $this->user->getIdentity();
        /** @var ModelLogin $login */
        $person = $login ? $login->person : null;
        return $person ? $person->getEventRoles($event) : [];
    }
}
