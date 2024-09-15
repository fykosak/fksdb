<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Events;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\Authorization\Resource\EventResource;
use Nette\Security\Permission;

class IsRegistrationOpened implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $resource = $acl->getQueriedResource();
        if ($resource instanceof EventResource) {
            return $resource->getEvent()->isRegistrationOpened();
        }
        var_dump(get_class($resource));
        throw new WrongAssertionException();
    }
}
