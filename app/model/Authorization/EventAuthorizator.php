<?php

namespace FKSDB\Authorization;

use FKSDB\Authorization\Assertions\EventOrgByIdAssertion;
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

    private IUserStorage $userStorage;

    private Permission $permission;

    private Context $context;

    private ContestAuthorizator $contestAuthorizator;

    public function __construct(IUserStorage $identity, Permission $acl, ContestAuthorizator $contestAuthorizator, Context $db) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->userStorage = $identity;
        $this->permission = $acl;
        $this->context = $db;
    }

    public function getUser(): IUserStorage {
        return $this->userStorage;
    }

    protected function getPermission(): Permission {
        return $this->permission;
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @param ModelEvent $event
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
     * @param IResource|string|null $resource
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
     * @param IResource|string|null $resource
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
        return (new EventOrgByIdAssertion($this->getUser(), $this->context))($this->getPermission(), null, $resource, $privilege, $event->event_id);
    }
}
