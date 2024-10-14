<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\ORM\Models\OrganizerModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Permission;

class IsSelfOrganizerAssertion implements Assertion
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
        $role = $acl->getQueriedRole();
        if (!$role instanceof OrganizerModel) {
            return false;
        }
        if ($model instanceof OrganizerModel) {
            return $model->person_id === $role->person_id;
        }
        throw new WrongAssertionException();
    }
}
