<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\ContestService;
use Nette\Security\Permission;
use Nette\Security\User;

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

    public function isAllowed(ContestResource $resource, ?string $privilege, ContestModel $contest): bool
    {
        if ($contest->contest_id !== $resource->getContest()->contest_id) {
            return false;
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
        return $this->baseAuthorizator->isAllowed($resource, $privilege);
    }

    public function isAllowedAnyContest(string $resource, ?string $privilege): bool
    {
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            if ($this->isAllowed(new PseudoContestResource($resource, $contest), $privilege, $contest)) {
                return true;
            }
        }
        return $this->baseAuthorizator->isAllowed($resource, $privilege);
    }
}
