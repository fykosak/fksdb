<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\User;
use Nette\SmartObject;

class ContestYearAuthorizator
{
    private User $user;
    private Permission $permission;
    private ContestAuthorizator $contestAuthorizator;

    public function __construct(
        User $identity,
        Permission $permission,
        ContestAuthorizator $contestAuthorizator
    ) {
        $this->user = $identity;
        $this->permission = $permission;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /**
     * @param Resource|string|null $resource
     */
    public function isAllowed($resource, ?string $privilege, ContestYearModel $contestYear): bool
    {
        if ($this->contestAuthorizator->isAllowed($resource, $privilege, $contestYear->contest)) {
            return true;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        if ($login) {
            foreach ($login->getImplicitContestYearRoles($contestYear) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return false;
    }
}
