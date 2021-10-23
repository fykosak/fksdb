<?php

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Events\Semantics\Role;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Transitions\Machine;
use Nette\Security\Permission;
use Nette\Security\Resource;

class PaymentAssertion
{

    /**
     * @param string|Role $role
     * @param string|Resource $resourceId
     */
    public function isPaymentEditable(Permission $acl, $role, $resourceId, ?string $privilege): bool
    {
        /** @var ModelPayment $payment */
        $payment = $acl->getQueriedResource();
        return \in_array($payment->state, [Machine\Machine::STATE_INIT, ModelPayment::STATE_NEW]);
    }
}
