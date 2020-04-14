<?php

namespace Authorization;

use Authorization\Assertions\EventOrgByIdAssertion;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Context;
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
     * @var Context
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
     * @param Context $db
     */
    function __construct(IUserStorage $identity, Permission $acl, ContestAuthorizator $contestAuthorizator, Context $db) {
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
     * @param $event
     * @return bool
     */
    public function isContestOrgAllowed($resource, $privilege, ModelEvent $event): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param $resource
     * @param $privilege
     * @param ModelEvent $event
     * @return bool
     */
    public function isEventOrContestOrgAllowed($resource, $privilege, ModelEvent $event) {
        if (!$this->getUser()->isAuthenticated()) {
            return false;
        }
        if ($this->isContestOrgAllowed($resource, $privilege, $event)) {
            return true;
        }
        return $this->isEventOrg($resource, $privilege, $event);
    }

    /**
     * @param $resource
     * @param $privilege
     * @param ModelEvent $event
     * @return bool
     */
    public function isEventAndContestOrgAllowed($resource, $privilege, ModelEvent $event) {
        if (!$this->getUser()->isAuthenticated()) {
            return false;
        }
        if (!$this->isEventOrg($resource, $privilege, $event)) {
            return false;
        }
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param $resource
     * @param $privilege
     * @param $event
     * @return bool
     */
    private function isEventOrg($resource, $privilege, ModelEvent $event): bool {
        return (new EventOrgByIdAssertion(null, $this->getUser(), $this->db))($this->getAcl(), null, $resource, $privilege, $event->event_id);
    }
}
