<?php

namespace Authorization;

use Authorization\Assertions\EventOrgByIdAssertion;
use ModelContest;
use ModelLogin;
use Nette\Database\Table\ActiveRow;
use Nette\Diagnostics\Debugger;
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
     * @param enum $privilege
     * @param int|ModelContest $contest queried contest
     * @return boolean
     */
    public function isAllowed($resource, $privilege, $contest) {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }
        $login = $this->getUser()->getIdentity();
        return $this->isAllowedForLogin($login, $resource, $privilege, $contest);
    }


    public function isAllowedForLogin(ModelLogin $login, $resource, $privilege, $contest) {
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

    public function isAllowedEvent($resource, $privilege, $event, $db) {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }
        $login = $this->getUser()->getIdentity();
        return $this->isAllowedToEventForLogin($login, $resource, $privilege, $event, $db) || $this->isAllowed($resource, $privilege, $event->event_type->contest_id);
    }

    public function isAllowedToEventForLogin(ModelLogin $login, $resource, $privilege, $event, $db) {
        $eventOrgByIdAssertion = new EventOrgByIdAssertion($event->event_type->event_type_id, $this->getUser(), $db);
        return $eventOrgByIdAssertion($this->acl, null, $resource, $privilege, $event->event_id);
    }
}
