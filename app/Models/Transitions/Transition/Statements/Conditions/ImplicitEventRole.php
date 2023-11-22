<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;

/**
 * @phpstan-template TModel of (\Nette\Security\Resource&\Fykosak\NetteORM\Model\Model)
 * @phpstan-extends EventRole<ModelHolder<FakeStringEnum&EnumColumn,TModel>>
 */
class ImplicitEventRole extends EventRole
{

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
