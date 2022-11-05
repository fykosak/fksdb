<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\Models\EventModel;

class ImplicitEventRole extends EventRole
{

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    public function __invoke(ModelHolder $holder): bool
    {
        /** @var EventModel $event */
        $event = $holder->getModel()->getReferencedModel(EventModel::class);
        return $this->eventAuthorizator->isAllowed($holder->getModel(), $this->privilege, $event);
    }
}
