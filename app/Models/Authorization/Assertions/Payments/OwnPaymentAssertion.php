<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Payments;

use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\Authorization\Roles\Base\LoggedInRole;
use FKSDB\Models\ORM\Models\PaymentModel;
use Nette\Security\Permission;

class OwnPaymentAssertion
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
        $model = $holder->getResource();
        if (!$model instanceof PaymentModel) {
            throw new WrongAssertionException();
        }
        $role = $acl->getQueriedRole();
        if ($role instanceof LoggedInRole) {
            return $role->getModel()->login_id === $model->person->getLogin()->login_id;
        }
        return false;
    }
}
