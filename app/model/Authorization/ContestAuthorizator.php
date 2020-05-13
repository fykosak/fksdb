<?php

namespace Authorization;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelRole;
use Nette\Database\Table\ActiveRow;
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

    /**
     * @var User
     */
    private $user;

    /**
     * @var Permission
     */
    private $acl;

    /**
     * ContestAuthorizator constructor.
     * @param User $identity
     * @param Permission $acl
     */
    public function __construct(User $identity, Permission $acl) {
        $this->user = $identity;
        $this->acl = $acl;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @return Permission
     */
    protected function getAcl() {
        return $this->acl;
    }

    /**
     * User must posses the role (for the resource:privilege) in the context
     * of the queried contest.
     *
     * @param IResource|string $resource
     * @param string $privilege
     * @param int|ModelContest $contest queried contest
     * @return bool
     */
    public function isAllowed($resource, string $privilege = null, $contest): bool {
        if (!$this->getUser()->isLoggedIn()) {
            $role = new Grant(Grant::CONTEST_ALL, ModelRole::GUEST);
            return $this->getAcl()->isAllowed($role, $resource, $privilege);
        }
        /**
         * @var ModelLogin $login
         */
        $login = $this->getUser()->getIdentity();
        return $this->isAllowedForLogin($login, $resource, $privilege, $contest);
    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    public final function isAllowedForAnyContest($resource, string $privilege = null): bool {
        if (!$this->getUser()->isLoggedIn()) {
            $role = new Grant(Grant::CONTEST_ALL, ModelRole::GUEST);
            return $this->getAcl()->isAllowed($role, $resource, $privilege);
        }
        /**
         * @var ModelLogin $login
         */
        $login = $this->getUser()->getIdentity();

        $roles = $login->getRoles();

        foreach ($roles as $role) {
            if ($this->acl->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ModelLogin $login
     * @param IResource|string $resource
     * @param string $privilege
     * @param ModelContest|int $contest
     * @return bool
     */
    public final function isAllowedForLogin(ModelLogin $login, $resource, string $privilege = null, $contest): bool {
        $contestId = ($contest instanceof ActiveRow) ? $contest->contest_id : $contest;
        $roles = $login->getRoles();

        foreach ($roles as $role) {
            if (($role->getContestId() !== Grant::CONTEST_ALL) && ($role->getContestId() != $contestId)) {
                continue;
            }
            if ($this->acl->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }
}
