<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Authorization\Authorizators\ContestAuthorizator;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;

/**
 * @phpstan-template TModel of (\Nette\Security\Resource&\Fykosak\NetteORM\Model\Model)
 * @phpstan-implements Statement<bool,ModelHolder<FakeStringEnum&EnumColumn,TModel>>
 */
class AnyContestRole implements Statement
{
    protected ContestAuthorizator $contestAuthorizator;
    protected ?string $privilege;

    public function __construct(?string $privilege, ContestAuthorizator $contestAuthorizator)
    {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->privilege = $privilege;
    }

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        return $this->contestAuthorizator->isAllowedAnyContest(
            $holder->getModel(),
            $this->privilege
        );
    }
}
