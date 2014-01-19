<?php

namespace Events\Semantics;

use Authorization\ContestAuthorizator;
use Nette\Object;
use Nette\Security\User;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @obsolete Needs refactoring due to ConditionEvaluator (for only contestans events)
 * @author Michal Koutný <michal@fykos.cz>
 */
class Role extends Object {
    use WithEventTrait;

    const GUEST = 'guest';
    const REGISTERED = 'registered';
    const ADMIN = 'admin';

    /**
     * @var string
     */
    private $role;

    /**
     * @var User
     */
    private $user;

    /**
     * @var ContestAuthorizator
     */
    private $authorizator;

    function __construct($role, User $user, ContestAuthorizator $authorizator) {
        $this->role = $role;
        $this->user = $user;
        $this->authorizator = $authorizator;
    }

    public function __invoke($obj) {
        switch ($this->role) {
            case self::ADMIN:
                $event = $this->getEvent($obj);
                return $this->authorizator->isAllowed($event, 'application', $event->getEventType()->contest);
            case self::REGISTERED:
                return $this->user->isLoggedIn();
            case self::GUEST:
                return true;
            default:
                return false;
        }
    }

}
