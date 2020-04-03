<?php

namespace FKSDB\Transitions\Statements\Conditions;

use FKSDB\ORM\Models\IEventReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Security\IResource;

/**
 * Class ImplicitEventRole
 * @package FKSDB\Transitions\Statements\Conditions
 */
class ImplicitEventRole extends EventRole {

    /**
     * @param array $args
     * @return bool
     * @throws BadRequestException
     */
    protected function evaluate(...$args): bool {
        list($model) = $args;

        if (!($model instanceof IEventReferencedModel) || !($model instanceof IResource)) {
            throw new BadRequestException();
        }
        return $this->eventAuthorizator->isContestOrgAllowed($model, $this->privilege, $model->getEvent());
    }
}
