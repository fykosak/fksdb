<?php

namespace Authorization;

use ModelContest;
use ModelLogin;
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
     * User must posses the role (for the resource:privilege) in the context
     * of the queried contest.
     *
     * @param mixed $resource
     * @param enum $privilege
     * @param int|ModelContest $contest queried contest
     * @return boolean
     */
    public function isAllowed(User $identity, Permission $acl, $resource, $privilege, $contest) {
        if (!$identity->getUser()->isLoggedIn()) {
            return false;
        }
        $login = $identity->getUser()->getIdentity();
        return $this->isAllowedForLogin($identity, $acl, $login, $resource, $privilege, $contest);
    }

    public final function isAllowedForLogin(User $identity, Permission $acl, ModelLogin $login, $resource, $privilege, $contest) {
        $contestId = ($contest instanceof ActiveRow) ? $contest->contest_id : $contest;
        $roles = $login->getRoles();

        foreach ($roles as $role) {
            if ($role->getContestId() != $contestId) {
                continue;
            }

            if ($acl->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }
}
