<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\Authorization\Resource\EventToContestYearResource;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\Security\Permission;
use Nette\Security\User;

final class EventAuthorizator
{
    private User $user;
    private Permission $permission;
    private ContestYearAuthorizator $contestYearAuthorizator;

    public function __construct(User $user, Permission $acl, ContestYearAuthorizator $contestYearAuthorizator)
    {
        $this->contestYearAuthorizator = $contestYearAuthorizator;
        $this->user = $user;
        $this->permission = $acl;
    }

    public function isAllowed(EventResource $resource, ?string $privilege, EventModel $event): bool
    {
        if ($event->event_id !== $resource->getEvent()->event_id) {
            return false;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        if ($login) {
            foreach ($login->getEventRoles($event) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return $this->contestYearAuthorizator->isAllowed(
            new EventToContestYearResource($resource),
            $privilege,
            $event->getContestYear()
        );
    }
}
