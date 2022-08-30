<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Expressions\EvaluatedExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\SmartObject;

/**
 * @obsolete Needs refactoring due to ConditionEvaluator (for only contestans events)
 */
class Role extends EvaluatedExpression
{
    use SmartObject;

    public const ADMIN = 'admin';

    private string $role;

    private EventAuthorizator $eventAuthorizator;

    public function __construct(
        string $role,
        EventAuthorizator $eventAuthorizator
    ) {
        $this->role = $role;
        $this->eventAuthorizator = $eventAuthorizator;
    }

    /**
     * @param BaseHolder $holder
     */
    public function __invoke(ModelHolder $holder): bool
    {
        switch ($this->role) {
            case self::ADMIN:
                return $this->eventAuthorizator->isAllowed($holder->event, 'application', $holder->event);
            default:
                return false;
        }
    }

    public function __toString(): string
    {
        return "role($this->role)";
    }
}
