<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Events\EventRole\EventRole;
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
     * @deprecated
     */
    public function isAllowed($resource, ?string $privilege, ModelEvent $event): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param Resource|string $resource
     */
    public function isContestOrgAllowed($resource, ?string $privilege, ModelEvent $event): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param Resource|string|null $resource
     */
    public function isEventOrContestOrgAllowed($resource, ?string $privilege, ModelEvent $event): bool
    {
        $login = $this->user->getIdentity();
        if (!$login) {
            return false;
        }
        if ($this->isContestOrgAllowed($resource, $privilege, $event)) {
            return true;
        }
        return $this->isEventOrg($event);
    }

    /**
     * @param ModelEvent $event
     * @return EventRole[]
     */
    private function getRolesForEvent(ModelEvent $event): array
    {
        $login = $this->user->getIdentity();
        /** @var ModelLogin $login */
        $person = $login ? $login->getPerson() : null;
        return $person ? $person->getEventRoles($event) : [];
    }

    private function isEventOrg(ModelEvent $event): bool
    {
        $login = $this->user->getIdentity();
        /** @var ModelLogin $login */
        $person = $login ? $login->getPerson() : null;
        if (!$person) {
            return false;
        }
        return $event->getEventOrgs()->where('person_id', $person->person_id)->count() > 0;
    }
}
