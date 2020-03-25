<?php

namespace FKSDB\Transitions\Statements\Conditions;

use FKSDB\ORM\Models\IEventReferencedModel;
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
     * @param array $args
     * @return bool
     * @throws BadRequestException
     */
    protected function evaluate(IStateModel $model = null, ...$args): bool {
        if (!($model instanceof IEventReferencedModel) || !($model instanceof IResource)) {
            throw new BadRequestException();
        }
        return $this->eventAuthorizator->isContestOrgAllowed($model, $this->privilege, $model->getEvent());
    }
}
