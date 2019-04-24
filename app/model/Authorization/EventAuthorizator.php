<?php

namespace Authorization;

use Authorization\Assertions\EventOrgByIdAssertion;
use Nette\Database\Connection;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Class EventAuthorizator
 * @package Authorization
 */
class EventAuthorizator {
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
     * @var Connection
     */
    private $db;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    /**
     * EventAuthorizator constructor.
     * @param User $identity
     * @param Permission $acl
     * @param ContestAuthorizator $contestAuthorizator
     * @param Connection $db
     */
    function __construct(User $identity, Permission $acl, ContestAuthorizator $contestAuthorizator, Connection $db) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $identity;
        $this->acl = $acl;
        $this->db = $db;
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
     * @param $resource
     * @param $privilege
     * @param $event
     * @return bool
     */
    public function isAllowed($resource, $privilege, $event) {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }
        return $this->isAllowedForLogin($resource, $privilege, $event) || $this->contestAuthorizator->isAllowed($resource, $privilege, $event->event_type->contest_id);
    }

    /**
     * @param $resource
     * @param $privilege
     * @param $event
     * @return bool
     */
    public function isAllowedForLogin($resource, $privilege, $event) {
        $eventOrgByIdAssertion = new EventOrgByIdAssertion($event->event_type->event_type_id, $this->getUser(), $this->db);
        return $eventOrgByIdAssertion($this->acl, null, $resource, $privilege, $event->event_id);
    }
}
