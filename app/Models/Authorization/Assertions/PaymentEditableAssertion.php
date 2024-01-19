<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PaymentModel;
use Nette\Security\Permission;

class PaymentEditableAssertion implements Assertion
{
    /**
     * @throws BadTypeException
     */
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $payment = $acl->getQueriedResource();
        if (!$payment instanceof PaymentModel) {
            throw new BadTypeException(PaymentModel::class, $payment);
        }
        return $payment->canEdit();
    }
}
