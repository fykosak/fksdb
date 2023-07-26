<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @obsolete Needs refactoring due to ConditionEvaluator (for only contestans events)
 * @implements Statement<bool>
 */
class Role implements Statement
{
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

    public function __invoke(...$args): bool
    {
        /** @var BaseHolder $holder */
        [$holder] = $args;
        switch ($this->role) {
            case self::ADMIN:
                return $this->eventAuthorizator->isAllowed($holder->getModel(), 'edit', $holder->event);
            default:
                return false;
        }
    }
}
