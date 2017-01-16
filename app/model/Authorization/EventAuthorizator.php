<?php

namespace Authorization;

use Authorization\Assertions\EventOrgByIdAssertion;
use Nette\Object;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\Database\Connection;

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
     * @var Connection
     */
    private $db;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    function __construct(User $identity, Permission $acl, ContestAuthorizator $contestAuthorizator, Connection $db) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $identity;
        $this->acl = $acl;
        $this->db = $db;
    }

    public function getUser() {
        return $this->user;
    }

    protected function getAcl() {
        return $this->acl;
    }

    public function isAllowed($resource, $privilege, $event) {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }
        return $this->isAllowedForLogin($resource, $privilege, $event) || $this->contestAuthorizator->isAllowed($resource, $privilege, $event->event_type->contest_id);
    }

    public function isAllowedForLogin($resource, $privilege, $event) {
        $eventOrgByIdAssertion = new EventOrgByIdAssertion($event->event_type->event_type_id, $this->getUser(), $this->db);
        return $eventOrgByIdAssertion($this->acl, null, $resource, $privilege, $event->event_id);
    }
}
