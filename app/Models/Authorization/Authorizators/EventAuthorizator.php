<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\User;
use Nette\SmartObject;

final class EventAuthorizator
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
    public function isAllowed($resource, ?string $privilege, EventModel $event): bool
    {
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        if ($login) {
            foreach ($login->getEventRoles($event) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->event_type->contest);
    }
}
