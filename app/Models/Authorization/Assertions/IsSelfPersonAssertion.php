<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\Contest\OrganizerRole;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Permission;

class IsSelfPersonAssertion implements Assertion
{
    /**
     * Check that the person is the person of logged user.
     *
     * @note Grant contest is ignored in this context (i.e. person is context-less).
     * @throws \ReflectionException
     */
    public function __invoke(Permission $acl): bool
    {
        $holder = $acl->getQueriedResource();
        /** @var Model $model */
        $model = $holder->getResource();
        if (!$model instanceof PersonModel) {
            throw new WrongAssertionException();
        }
        $role = $acl->getQueriedRole();
        if ($role instanceof OrganizerRole) {
            return $role->getModel()->person_id === $model->person_id;
        }
        return false;
    }
}
