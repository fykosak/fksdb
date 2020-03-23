<?php

namespace Authorization;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
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
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    /**
     * EventAuthorizator constructor.
     * @param IUserStorage $identity
     * @param ContestAuthorizator $contestAuthorizator
     */
    function __construct(IUserStorage $identity, ContestAuthorizator $contestAuthorizator) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $identity;
    }

    /**
     * @return IUserStorage
     */
    public function getUser(): IUserStorage {
        return $this->user;
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
        return $this->isEventOrg($event);
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
        // Bypass for cartesian
        // TODO bypass to other roles
        if ($this->contestAuthorizator->isAllowed(Permission::ALL, Permission::ALL, $event->getContest())) {
            return true;
        }
        if (!$this->isEventOrg($event)) {
            return false;
        }
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $event->getContest());
    }

    /**
     * @param ModelEvent $event
     * @return bool
     */
    private function isEventOrg(ModelEvent $event): bool {
        $identity = $this->getUser()->getIdentity();
        if (!$identity) {
            return false;
        }
        /**
         * @var ModelPerson $person
         */
        $person = $identity->getPerson();
        if (!$person) {
            return false;
        }
        return $person->isEventOrg($event);
    }
}
