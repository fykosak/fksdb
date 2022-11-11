<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use Nette\Security\Permission;

class PaymentEditableAssertion implements Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        /** @var PaymentModel|null $payment */
        $payment = $acl->getQueriedResource();
        return !isset($payment) || $payment->state === PaymentState::New;
    }
}
