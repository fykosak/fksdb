<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Authorization\Authorizators\EventAuthorizator;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of (\Nette\Security\Resource&\Fykosak\NetteORM\Model\Model)
 * @phpstan-implements Statement<bool,ModelHolder<TModel,FakeStringEnum&EnumColumn>>
 */
class EventRole implements Statement
{
    protected EventAuthorizator $eventAuthorizator;
    protected ?string $privilege;

    public function __construct(string $privilege, Container $container)
    {
        $container->callInjects($this);
        $this->privilege = $privilege;
    }

    public function inject(EventAuthorizator $eventAuthorizator): void
    {
        $this->eventAuthorizator = $eventAuthorizator;
    }

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        /** @var EventModel $event */
        $event = $holder->getModel()->getReferencedModel(EventModel::class);
        return $this->eventAuthorizator->isAllowed($holder->getModel(), $this->privilege, $event);
    }
}
