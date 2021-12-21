<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelRole;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\SmartObject;

class ContestAuthorizator
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
    public function isAllowed($resource, ?string $privilege, ModelContest $contest): bool
    {
        if (!$this->getUser()->isLoggedIn()) {
            $role = new Grant(ModelRole::GUEST, null);
            return $this->getPermission()->isAllowed($role, $resource, $privilege);
        }
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        return $this->isAllowedForLogin($login, $resource, $privilege, $contest);
    }

    /**
     * @param Resource|string|null $resource
     */
    final public function isAllowedForAnyContest($resource, ?string $privilege): bool
    {
        if (!$this->getUser()->isLoggedIn()) {
            $role = new Grant(ModelRole::GUEST, null);
            return $this->getPermission()->isAllowed($role, $resource, $privilege);
        }
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();

        $roles = $login->getRoles();

        foreach ($roles as $role) {
            if ($this->getPermission()->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Resource|string $resource
     * @param ModelContest|int $contest
     */
    final public function isAllowedForLogin(
        ModelLogin $login,
        $resource,
        ?string $privilege,
        ModelContest $contest
    ): bool {
        $roles = $login->getRoles();

        foreach ($roles as $role) {
            if (!is_null($role->getContest()) && ($role->getContest()->contest_id !== $contest->contest_id)) {
                continue;
            }
            if ($this->getPermission()->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }
}
