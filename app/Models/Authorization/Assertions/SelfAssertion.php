<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\Base\LoggedInRole;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\Permission;

class SelfAssertion implements Assertion
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
        if ($role instanceof LoggedInRole) {
            $person = null;
            try {
                $person = $model->getReferencedModel(PersonModel::class);
            } catch (CannotAccessModelException $exception) {
            }

            if (!$person instanceof PersonModel) {
                return false;
            }
            return $role->getModel()->login_id === $person->getLogin()->login_id;
        }
        return false;
    }
}
