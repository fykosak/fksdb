<?php

namespace FKSDB\Transitions\Statements\Conditions;

use FKSDB\Transitions\IEventReferencedModel;
use FKSDB\Transitions\IStateModel;
use Nette\Application\BadRequestException;
use Nette\Security\IResource;

/**
 * Class ImplicitEventRole
 * @package FKSDB\Transitions\Statements\Conditions
 */
class ImplicitEventRole extends EventRole {

    /**
     * @param IStateModel|null $model
     * @return bool
     * @throws BadRequestException
     */
    protected function evaluate(IStateModel $model = null): bool {
        if (!($model instanceof IEventReferencedModel) || !($model instanceof IResource)) {
            throw new BadRequestException();
        }
        return $this->eventAuthorizator->isAllowed($model, $this->privilege, $model->getEvent());
    }
}
