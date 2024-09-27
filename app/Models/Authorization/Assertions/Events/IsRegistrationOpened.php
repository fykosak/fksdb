<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Events;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use Nette\Security\Permission;

class IsRegistrationOpened implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $holder = $acl->getQueriedResource();
        if ($holder instanceof EventResourceHolder) {
            return $holder->getContext()->isRegistrationOpened();
        }
        throw new WrongAssertionException();
    }
}
