<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;

/**
 * @phpstan-implements Statement<bool,ModelHolder<PaymentModel,FakeStringEnum&EnumColumn>>
 */
class AnyContestRole implements Statement
{
    protected Authorizator $authorizator;
    protected ?string $privilege;

    public function __construct(?string $privilege, Authorizator $authorizator)
    {
        $this->authorizator = $authorizator;
        $this->privilege = $privilege;
    }

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        /* return $this->authorizator->isAllowedEvent(
             new PseudoEventResource($holder->getModel(), $holder->getModel()->getEvent()),
             $this->privilege,
             $holder->getModel()->getEvent()
         );*/
        // TODO
        return false;
    }
}
