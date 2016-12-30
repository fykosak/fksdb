<?php

namespace Authorization;

use Authorization\Assertions\EventOrgByIdAssertion;
use Nette\Object;
use Nette\Security\Permission;
use Nette\Security\User;

class EventAuthorizator extends Object {
    /**
     * @var User
     */
    private $user;

    /**
     * @var Permission
     */
    private $acl;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    function __construct(User $identity, Permission $acl, ContestAuthorizator $contestAuthorizator) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $identity;
        $this->acl = $acl;
    }

    public function getUser() {
        return $this->user;
    }

    protected function getAcl() {
        return $this->acl;
    }

    public function isAllowed($resource, $privilege, $event, $db) {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }
        return $this->isAllowedForLogin($resource, $privilege, $event, $db) || $this->contestAuthorizator->isAllowed($resource, $privilege, $event->event_type->contest_id);
    }

    public function isAllowedForLogin($resource, $privilege, $event, $db) {
        $eventOrgByIdAssertion = new EventOrgByIdAssertion($event->event_type->event_type_id, $this->getUser(), $db);
        return $eventOrgByIdAssertion($this->acl, null, $resource, $privilege, $event->event_id);
    }
}
