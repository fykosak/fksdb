<?php

namespace Authorization;

use Authorization\Assertions\EventOrgByIdAssertion;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Connection;
use Nette\Security\IUserStorage;
use Nette\Security\Permission;
use Nette\SmartObject;

/**
 * Class EventAuthorizator
 * @package Authorization
 */
class EventAuthorizator {
    use SmartObject;
    /**
     * @var IUserStorage
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
     * @param IUserStorage $identity
     * @param Permission $acl
     * @param ContestAuthorizator $contestAuthorizator
     * @param Connection $db
     */
    function __construct(IUserStorage $identity, Permission $acl, ContestAuthorizator $contestAuthorizator, Connection $db) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $identity;
        $this->acl = $acl;
        $this->db = $db;
    }

    /**
     * @return IUserStorage
     */
    public function getUser(): IUserStorage {
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
     * @deprecated
     */
    public function isAllowed($resource, $privilege, ModelEvent $event): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param $resource
     * @param $privilege
     * @param ModelEvent $event
     * @return bool
     */
    public function isEventOrgAllowed($resource, $privilege, ModelEvent $event) {
        if (!$this->getUser()->isAuthenticated()) {
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
    public function isAllowedForLogin($resource, $privilege, $event): bool {
        return (new EventOrgByIdAssertion($event->event_type->event_type_id, $this->getUser(), $this->db))($this->getAcl(), null, $resource, $privilege, $event->event_id);
    }
}
