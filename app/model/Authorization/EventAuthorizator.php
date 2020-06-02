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

    private IUserStorage $user;

    private Permission $acl;

    private Context $db;

    private ContestAuthorizator $contestAuthorizator;

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
     * @param string|IResource $resource
     * @param $privilege
     * @param $event
     * @return bool
     * @deprecated
     */
    public function isAllowed($resource, ?string $privilege, ModelEvent $event): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param IResource|string $resource
     * @param string|null $privilege
     * @param ModelEvent $event
     * @return bool
     */
    public function isContestOrgAllowed($resource, ?string $privilege, ModelEvent $event): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param string|IResource $resource
     * @param string|null $privilege
     * @param ModelEvent $event
     * @return bool
     */
    public function isEventOrContestOrgAllowed($resource, ?string $privilege, ModelEvent $event): bool {
        if (!$this->getUser()->isAuthenticated()) {
            return false;
        }
        if ($this->isContestOrgAllowed($resource, $privilege, $event)) {
            return true;
        }
        return $this->isEventOrg($resource, $privilege, $event);
    }

    /**
     * @param IResource|string $resource
     * @param string|null $privilege
     * @param ModelEvent $event
     * @return bool
     */
    public function isEventAndContestOrgAllowed($resource, ?string $privilege, ModelEvent $event): bool {
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
     * @param string|null $privilege
     * @param ModelEvent $event
     * @return bool
     */
    private function isEventOrg($resource, ?string $privilege, ModelEvent $event): bool {
        return (new EventOrgByIdAssertion(null, $this->getUser(), $this->db))($this->getAcl(), null, $resource, $privilege, $event->event_id);
    }
}
