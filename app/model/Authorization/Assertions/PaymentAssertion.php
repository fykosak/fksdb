<?php

namespace FKSDB\Authorization\Assertions;

use FKSDB\ORM\Models\ModelPayment;
use FKSDB\Transitions\Machine;
use Nette\InvalidStateException;
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
     * @throws InvalidStateException
     */
    public function isPaymentEditable(Permission $acl, $role, $resourceId, $privilege): bool {
        /** @var ModelPayment $payment */
        $payment = $acl->getQueriedResource();
        return \in_array($payment->getState(), [Machine\Machine::STATE_INIT, ModelPayment::STATE_NEW]);
    }
}
