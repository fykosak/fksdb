<?php

namespace FKSDB\Transitions\Transition\Statements\Conditions;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\IEventReferencedModel;
use Nette\Security\IResource;

/**
 * Class ImplicitEventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ImplicitEventRole extends EventRole {

    /**
     * @param array $args
     * @return bool
     * @throws BadTypeException
     */
    protected function evaluate(...$args): bool {
        [$model] = $args;
        if (!($model instanceof IEventReferencedModel) || !($model instanceof IResource)) {
            throw new BadTypeException(IResource::class, $model);
        }
        return $this->eventAuthorizator->isContestOrgAllowed($model, $this->privilege, $model->getEvent());
    }
}
