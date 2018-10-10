<?php

namespace Authorization;

use Authorization\Assertions\EventOrgByIdAssertion;
use Nette\Database\Connection;
use Nette\Object;
use Nette\Security\Permission;
use Nette\Security\User;

class EventAuthorizator extends Object {
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    function __construct(ContestAuthorizator $contestAuthorizator, Connection $db) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->db = $db;
    }

    public function isAllowed(User $identity, Permission $acl, $resource, $privilege, $event) {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }
        return $this->isAllowedForLogin($identity, $acl, $resource, $privilege, $event) || $this->contestAuthorizator->isAllowed($identity, $acl, $resource, $privilege, $event->event_type->contest_id);
    }

    public function isAllowedForLogin(User $identity, Permission $acl, $resource, $privilege, $event) {
        $eventOrgByIdAssertion = new EventOrgByIdAssertion($event->event_type->event_type_id, $this->getUser(), $this->db);
        return $eventOrgByIdAssertion($acl, null, $resource, $privilege, $event->event_id);
    }
}
