<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Payments;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use Nette\Security\Permission;

class PaymentEditableAssertion implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $holder = $acl->getQueriedResource();
        $payment = $holder->getResource();
        if ($payment instanceof PaymentModel) {
            return $payment->state === PaymentState::InProgress;
        }
        throw new WrongAssertionException();
    }
}
