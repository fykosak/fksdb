<?php

namespace FKSDB\Transitions\Statements\Conditions;

use FKSDB\Transitions\IEventReferencedModel;
use FKSDB\Transitions\IStateModel;
use Nette\Application\BadRequestException;
use Nette\Security\IResource;

class ImplicitEventRole extends EventRole {

    protected function evaluate(IStateModel $model = null): bool {
        if (!($model instanceof IEventReferencedModel) || !($model instanceof IResource)) {
            throw new BadRequestException();
        }
        return $this->eventAuthorizator->isAllowed($model, $this->privilege, $model->getEvent());
    }
}
