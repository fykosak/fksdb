<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\EventModel;

class ImplicitEventRole extends EventRole
{

    /**
     * @param Model[] $args
     * @throws BadTypeException
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    protected function evaluate(...$args): bool
    {
        [$holder] = $args;
        if (!$holder instanceof ModelHolder) {
            throw new BadTypeException(ModelHolder::class, $holder);
        }
        /** @var EventModel $event */
        $event = $holder->getModel()->getReferencedModel(EventModel::class);
        return $this->eventAuthorizator->isAllowed($holder->getModel(), $this->privilege, $event);
    }
}
