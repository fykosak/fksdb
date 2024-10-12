<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\Contest\OrganizerRole;
use FKSDB\Models\ORM\Models\OrganizerModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Permission;

class IsSelfOrganizerAssertion implements Assertion
{
    /**
     * @throws \ReflectionException
     */
    public function __invoke(Permission $acl): bool
    {
        $holder = $acl->getQueriedResource();
        /** @var Model $model */
        $model = $holder->getResource();
        $role = $acl->getQueriedRole();
        if ($model instanceof OrganizerModel && $role instanceof OrganizerRole) {
            return $model->person_id === $role->getModel()->person_id;
        }
        throw new WrongAssertionException();
    }
}
