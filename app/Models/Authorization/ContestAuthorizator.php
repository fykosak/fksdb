<?php

namespace FKSDB\Models\Authorization;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelRole;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Security\IResource;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestAuthorizator {

    use SmartObject;

    private User $user;

    private Permission $permission;

    public function __construct(User $identity, Permission $permission) {
        $this->user = $identity;
        $this->permission = $permission;
    }

    public function getUser(): User {
        return $this->user;
    }

    protected function getPermission(): Permission {
        return $this->permission;
    }

    /**
     * User must posses the role (for the resource:privilege) in the context
     * of the queried contest.
     *
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @param int|ModelContest $contest queried contest
     * @return bool
     * @throws InvalidStateException
     */
    public function isAllowed($resource, ?string $privilege, $contest): bool {
        if (!$this->getUser()->isLoggedIn()) {
            $role = new Grant(Grant::CONTEST_ALL, ModelRole::GUEST);
            return $this->getPermission()->isAllowed($role, $resource, $privilege);
        }
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        return $this->isAllowedForLogin($login, $resource, $privilege, $contest);
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * @throws InvalidStateException
     */
    final public function isAllowedForAnyContest($resource, ?string $privilege): bool {
        if (!$this->getUser()->isLoggedIn()) {
            $role = new Grant(Grant::CONTEST_ALL, ModelRole::GUEST);
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
     * @param ModelLogin $login
     * @param IResource|string $resource
     * @param string|null $privilege
     * @param ModelContest|int $contest
     * @return bool
     * @throws InvalidStateException
     */
    final public function isAllowedForLogin(ModelLogin $login, $resource, $privilege, $contest): bool {
        $contestId = ($contest instanceof ActiveRow) ? $contest->contest_id : $contest;
        $roles = $login->getRoles();

        foreach ($roles as $role) {
            if (($role->getContestId() !== Grant::CONTEST_ALL) && ($role->getContestId() != $contestId)) {
                continue;
            }
            if ($this->getPermission()->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }
}
