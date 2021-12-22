<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Transitions\Machine;
use Nette\Security\Permission;

class PaymentEditableAssertion implements Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        /** @var ModelPayment $payment */
        $payment = $acl->getQueriedResource();
        return \in_array($payment->state, [Machine\Machine::STATE_INIT, ModelPayment::STATE_NEW]);
    }
}
