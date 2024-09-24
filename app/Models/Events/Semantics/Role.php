<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @obsolete Needs refactoring due to ConditionEvaluator (for only contestans events)
 * @implements Statement<bool,\FKSDB\Models\Transitions\Holder\ParticipantHolder>
 */
class Role implements Statement
{
    public const ADMIN = 'admin';

    private string $role;

    private Authorizator $authorizator;

    public function __construct(
        string $role,
        Authorizator $authorizator
    ) {
        $this->role = $role;
        $this->authorizator = $authorizator;
    }

    public function __invoke(...$args): bool
    {
        /** @var ParticipantHolder $holder */
        [$holder] = $args;
        switch ($this->role) {
            case self::ADMIN:
                return $this->authorizator->isAllowedEvent(
                    $holder->getModel(),
                    'organizer',
                    $holder->getModel()->event
                );
            default:
                return false;
        }
    }
}
