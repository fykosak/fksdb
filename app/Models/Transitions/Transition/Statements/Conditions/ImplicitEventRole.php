<?php

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\ReferencedFactory;
use Nette\Security\Resource;

/**
 * Class ImplicitEventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ImplicitEventRole extends EventRole {

    /**
     * @param AbstractModelSingle[] $args
     * @return bool
     * @throws BadTypeException
     * @throws CannotAccessModelException
     */
    protected function evaluate(...$args): bool {
        [$model] = $args;
        if (!$model instanceof Resource) {
            throw new BadTypeException(Resource::class, $model);
        }
        /** @var ModelEvent $event */
        $event = ReferencedFactory::accessModel($model, ModelEvent::class);
        return $this->eventAuthorizator->isContestOrgAllowed($model, $this->privilege, $event);
    }
}
