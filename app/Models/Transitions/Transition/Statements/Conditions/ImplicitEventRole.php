<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\ReferencedAccessor;
use Nette\Security\Resource;

class ImplicitEventRole extends EventRole {

    /**
     * @param AbstractModel[] $args
     * @throws BadTypeException
     * @throws CannotAccessModelException
     */
    protected function evaluate(...$args): bool {
        [$model] = $args;
        if (!$model instanceof Resource) {
            throw new BadTypeException(Resource::class, $model);
        }
        /** @var ModelEvent $event */
        $event = ReferencedAccessor::accessModel($model, ModelEvent::class);
        return $this->eventAuthorizator->isAllowed($model, $this->privilege, $event);
    }
}
