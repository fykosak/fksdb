<?php

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Authorization\RelatedPersonAuthorizator;
use FKSDB\Models\Expressions\EvaluatedExpression;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * @obsolete Needs refactoring due to ConditionEvaluator (for only contestans events)
 */
class Role extends EvaluatedExpression
{
    use SmartObject;
    use WithEventTrait;

    public const GUEST = 'guest';
    public const REGISTERED = 'registered';
    public const RELATED = 'related';
    public const ADMIN = 'admin';

    private string $role;

    private User $user;

    private ContestAuthorizator $contestAuthorizator;

    private RelatedPersonAuthorizator $relatedAuthorizator;

    public function __construct(
        string $role,
        User $user,
        ContestAuthorizator $contestAuthorizator,
        RelatedPersonAuthorizator $relatedAuthorizator
    ) {
        $this->role = $role;
        $this->user = $user;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->relatedAuthorizator = $relatedAuthorizator;
    }

    public function __invoke(...$args): bool
    {
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

    public function __toString(): string
    {
        return "role({$this->role})";
    }
}
