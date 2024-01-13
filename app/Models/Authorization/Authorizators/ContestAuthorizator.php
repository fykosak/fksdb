<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\User;
use Nette\SmartObject;

final class ContestAuthorizator
{
    use SmartObject;

    private User $user;
    private Permission $permission;
    private BaseAuthorizator $baseAuthorizator;

    public function __construct(User $identity, Permission $permission, BaseAuthorizator $baseAuthorizator)
    {
        $this->user = $identity;
        $this->permission = $permission;
        $this->baseAuthorizator = $baseAuthorizator;
    }

    /**
     * @param Resource|string|null $resource
     */
    public function isAllowed($resource, ?string $privilege, ContestModel $contest): bool
    {
        if ($this->baseAuthorizator->isAllowed($resource, $privilege)) {
            return true;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        if ($login) {
            foreach ($login->getRoles() as $role) {
                if ($role->getContest()->contest_id === $contest->contest_id) {
                    if ($this->permission->isAllowed($role, $resource, $privilege)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param Resource|string|null $resource
     */
    public function isAllowedAnyContest($resource, ?string $privilege): bool
    {
        if ($this->baseAuthorizator->isAllowed($resource, $privilege)) {
            return true;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        if ($login) {
            foreach ($login->getRoles() as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return false;
    }
}
