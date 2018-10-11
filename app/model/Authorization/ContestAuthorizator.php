<?php

namespace Authorization;

use FKSDB\ORM\ModelContest;
use FKSDB\ORM\ModelLogin;
use Nette\Database\Table\ActiveRow;
use Nette\Object;
use Nette\Security\Permission;
use Nette\Security\User;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestAuthorizator extends Object {

    /**
     * @var User
     */
    private $user;

    /**
     * @var Permission
     */
    private $acl;

    function __construct(User $identity, Permission $acl) {
        $this->user = $identity;
        $this->acl = $acl;
    }

    public function getUser() {
        return $this->user;
    }

    protected function getAcl() {
        return $this->acl;
    }

    /**
     * User must posses the role (for the resource:privilege) in the context
     * of the queried contest.
     *
     * @param mixed $resource
     * @param string $privilege
     * @param int|\FKSDB\ORM\ModelContest $contest queried contest
     * @return boolean
     */
    public function isAllowed($resource, $privilege, $contest) {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }
        $login = $this->getUser()->getIdentity();
        return $this->isAllowedForLogin($login, $resource, $privilege, $contest);
    }

    public final function isAllowedForLogin(ModelLogin $login, $resource, $privilege, $contest) {
        $contestId = ($contest instanceof ActiveRow) ? $contest->contest_id : $contest;
        $roles = $login->getRoles();

        foreach ($roles as $role) {
            if ($role->getContestId() != $contestId) {
                continue;
            }

            if ($this->acl->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }
}
