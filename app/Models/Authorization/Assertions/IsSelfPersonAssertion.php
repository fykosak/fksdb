<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\Base\LoggedInRole;
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
        if ($role instanceof LoggedInRole) {
            return $role->getModel()->login_id === $model->getLogin()->login_id;
        }
        return false;
    }
}
