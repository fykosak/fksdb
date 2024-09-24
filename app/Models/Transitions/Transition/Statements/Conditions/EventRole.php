<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of (EventResource&Model)
 * @phpstan-implements Statement<bool,ModelHolder<TModel,FakeStringEnum&EnumColumn>>
 */
class EventRole implements Statement
{
    protected Authorizator $authorizator;
    protected ?string $privilege;

    public function __construct(string $privilege, Container $container)
    {
        $container->callInjects($this);
        $this->privilege = $privilege;
    }

    public function inject(Authorizator $authorizator): void
    {
        $this->authorizator = $authorizator;
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
        return $this->authorizator->isAllowedEvent($holder->getModel(), $this->privilege, $event);
    }
}
