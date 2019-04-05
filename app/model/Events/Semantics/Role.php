<?php

namespace Events\Semantics;

use Authorization\ContestAuthorizator;
use Authorization\RelatedPersonAuthorizator;
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
    const RELATED = 'related';
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
    private $contestAuthorizator;

    /**
     *
     * @var RelatedPersonAuthorizator
     */
    private $relatedAuthorizator;

    /**
     * Role constructor.
     * @param $role
     * @param User $user
     * @param ContestAuthorizator $contestAuthorizator
     * @param RelatedPersonAuthorizator $relatedAuthorizator
     */
    function __construct($role, User $user, ContestAuthorizator $contestAuthorizator, RelatedPersonAuthorizator $relatedAuthorizator) {
        $this->role = $role;
        $this->user = $user;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->relatedAuthorizator = $relatedAuthorizator;
    }

    /**
     * @param $obj
     * @return bool
     */
    public function __invoke($obj) {
        switch ($this->role) {
            case self::ADMIN:
                $event = $this->getEvent($obj);
                return $this->contestAuthorizator->isAllowed($event, 'application', $event->getEventType()->contest);
            case self::RELATED:
                $event = $this->getEvent($obj);
                return $this->relatedAuthorizator->isRelatedPerson($this->getHolder($obj));
            case self::REGISTERED:
                return $this->user->isLoggedIn();
            case self::GUEST:
                return true;
            default:
                return false;
        }
    }

    /**
     * @return string
     */
    public function __toString() {
        return "role({$this->role})";
    }

}
