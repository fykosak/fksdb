<?php

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\Transitions\Machine;
use Nette\Security\Permission;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PaymentAssertion {

    /**
     *
     * @param Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     */
    public function isPaymentEditable(Permission $acl, $role, $resourceId, $privilege): bool {
        /** @var ModelPayment $payment */
        $payment = $acl->getQueriedResource();
        return \in_array($payment->state, [Machine\Machine::STATE_INIT, ModelPayment::STATE_NEW]);
    }
}
