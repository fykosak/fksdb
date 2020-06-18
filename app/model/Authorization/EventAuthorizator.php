<?php

namespace Authorization;

use Authorization\Assertions\EventOrgByIdAssertion;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Context;
use Nette\Security\IResource;
use Nette\Security\IUserStorage;
use Nette\Security\Permission;
use Nette\SmartObject;

/**
 * Class EventAuthorizator
 * *
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
    public function __construct(IUserStorage $identity, Permission $acl, ContestAuthorizator $contestAuthorizator, Context $db) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $identity;
        $this->acl = $acl;
        $this->db = $db;
    }

    public function getUser(): IUserStorage {
        return $this->user;
    }

    protected function getAcl(): Permission {
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
     * @param IResource|string $resource
     * @param $privilege
     * @param $event
     * @return bool
     */
    public function isContestOrgAllowed($resource, $privilege, ModelEvent $event): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @param ModelEvent $event
     * @return bool
     */
    public function isEventOrContestOrgAllowed($resource, $privilege, ModelEvent $event): bool {
        if (!$this->getUser()->isAuthenticated()) {
            return false;
        }
        if ($this->isContestOrgAllowed($resource, $privilege, $event)) {
            return true;
        }
        return $this->isEventOrg($resource, $privilege, $event);
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @param ModelEvent $event
     * @return bool
     */
    public function isEventAndContestOrgAllowed($resource, $privilege, ModelEvent $event): bool {
        if (!$this->getUser()->isAuthenticated()) {
            return false;
        }
        if (!$this->isEventOrg($resource, $privilege, $event)) {
            return false;
        }
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param IResource|string $resource
     * @param $privilege
     * @param ModelEvent $event
     * @return bool
     */
    private function isEventOrg($resource, $privilege, ModelEvent $event): bool {
        return (new EventOrgByIdAssertion($this->getUser(), $this->db))($this->getAcl(), null, $resource, $privilege, $event->event_id);
    }
}
