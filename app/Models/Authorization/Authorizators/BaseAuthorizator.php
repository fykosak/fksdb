<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\Authorization\BaseRole;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\User;
use Nette\SmartObject;

final class BaseAuthorizator
{
    use SmartObject;

    private User $user;
    private Permission $permission;

    public function __construct(User $identity, Permission $permission)
    {
        $this->user = $identity;
        $this->permission = $permission;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    protected function getPermission(): Permission
    {
        return $this->permission;
    }

    /**
     * User must posses the role (for the resource:privilege) in the context
     * of the queried contest.
     *
     * @param Resource|string|null $resource
     */
    public function isAllowed($resource, ?string $privilege): bool
    {
        if (!$this->getUser()->isLoggedIn()) {
            return $this->getPermission()->isAllowed(new BaseRole(BaseRole::Guest), $resource, $privilege);
        } else {
            return $this->getPermission()->isAllowed(new BaseRole(BaseRole::Registered), $resource, $privilege);
        }
    }
}
