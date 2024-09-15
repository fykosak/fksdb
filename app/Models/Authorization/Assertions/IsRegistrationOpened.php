<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

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
        throw new WrongAssertionException();
    }
}
