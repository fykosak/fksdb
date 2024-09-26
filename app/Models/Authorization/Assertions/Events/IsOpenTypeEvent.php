<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Events;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\Authorization\Resource\EventResource;
use Nette\Security\Permission;

class IsOpenTypeEvent implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $holder = $acl->getQueriedResource();
        $resource = $holder->getResource();
        if ($resource instanceof EventResource) {
            return $resource->getEvent()->event_type->isOpenType();
        }
        throw new WrongAssertionException();
    }
}
