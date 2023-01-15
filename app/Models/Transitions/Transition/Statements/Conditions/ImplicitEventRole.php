<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\Models\EventModel;

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
