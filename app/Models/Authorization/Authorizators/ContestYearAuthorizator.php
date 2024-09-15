<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\Authorization\Resource\ContestYearResource;
use FKSDB\Models\Authorization\Resource\ContestYearToContestResource;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\Security\Permission;
use Nette\Security\User;

final class ContestYearAuthorizator
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

    public function isAllowed(ContestYearResource $resource, ?string $privilege, ContestYearModel $contestYear): bool
    {
        if (
            $contestYear->contest_id !== $resource->getContestYear()->contest_id
            || $contestYear->year !== $resource->getContestYear()->year
        ) {
            return false;
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
        return $this->contestAuthorizator->isAllowed(
            new ContestYearToContestResource($resource),
            $privilege,
            $contestYear->contest
        );
    }
}
