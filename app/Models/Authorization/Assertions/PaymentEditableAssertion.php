<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\ORM\Models\PaymentModel;
use Nette\Security\Permission;

class PaymentEditableAssertion implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $payment = $acl->getQueriedResource();
        if (!$payment instanceof PaymentModel) {
            throw new WrongAssertionException();
        }
        return $payment->canEdit();
    }
}
