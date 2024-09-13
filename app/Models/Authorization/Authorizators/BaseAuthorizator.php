<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\Authorization\Roles\Base\GuestRole;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\User;

final class BaseAuthorizator
{
    private User $user;
    private Permission $permission;

    public function __construct(User $identity, Permission $permission)
    {
        $this->user = $identity;
        $this->permission = $permission;
    }

    /**
     * @param Resource|string|null $resource
     */
    public function isAllowed($resource, ?string $privilege): bool
    {
        if (!$this->user->isLoggedIn()) {
            return $this->permission->isAllowed(new GuestRole(), $resource, $privilege);
        }
        /** @var LoginModel $login */
        $login = $this->user->identity;
        foreach ($login->getRoles() as $role) {
            if ($this->permission->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }
        return false;
    }
}
