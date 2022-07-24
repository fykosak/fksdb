<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\Transitions\Machine;
use Nette\Security\Permission;

class PaymentEditableAssertion implements Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        /** @var PaymentModel $payment */
        $payment = $acl->getQueriedResource();
        return \in_array($payment->state->value, [Machine\AbstractMachine::STATE_INIT, PaymentState::NEW]);
    }
}
