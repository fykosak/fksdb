<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\ContestService;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\User;
use Nette\SmartObject;

final class ContestAuthorizator
{
    private User $user;
    private Permission $permission;
    private BaseAuthorizator $baseAuthorizator;
    private ContestService $contestService;

    public function __construct(
        User $identity,
        Permission $permission,
        BaseAuthorizator $baseAuthorizator,
        ContestService $contestService
    ) {
        $this->user = $identity;
        $this->permission = $permission;
        $this->baseAuthorizator = $baseAuthorizator;
        $this->contestService = $contestService;
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
            foreach ($login->getContestRoles($contest) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
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
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            if ($this->isAllowed($resource, $privilege, $contest)) {
                return true;
            }
        }
        return false;
    }
}
