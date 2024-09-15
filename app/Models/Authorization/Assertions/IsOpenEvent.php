<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Resource\EventResource;
use Nette\Security\Permission;

class IsOpenEvent implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $resource = $acl->getQueriedResource();
        if ($resource instanceof EventResource) {
            switch ($resource->getEvent()->event_type_id) {
                case 1: // FOF
                case 9: // FOL
                case 11: // setkani 2x
                case 12:
                case 2: // DSEF 2x
                case 14:
                    return true;
                default:
                    return false;
            }
        }
        throw new WrongAssertionException();
    }
}
