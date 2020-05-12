<?php

namespace FKSDB\Events\Semantics;

use Authorization\ContestAuthorizator;
use Authorization\RelatedPersonAuthorizator;
use FKSDB\Expressions\EvaluatedExpression;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @obsolete Needs refactoring due to ConditionEvaluator (for only contestans events)
 * @author Michal Koutný <michal@fykos.cz>
 */
class Role extends EvaluatedExpression {

    use SmartObject;
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
     * @param array $args
     * @return bool
     */
    public function __invoke(...$args): bool {
        switch ($this->role) {
            case self::ADMIN:
                $event = $this->getEvent($args[0]);
                return $this->contestAuthorizator->isAllowed($event, 'application', $event->getContest());
            case self::RELATED:
                return $this->relatedAuthorizator->isRelatedPerson($this->getHolder($args[0]));
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
