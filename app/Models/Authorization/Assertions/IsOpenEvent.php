<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\PseudoEventResource;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Security\Permission;

class IsOpenEvent implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $resource = $acl->getQueriedResource();
        if (
            $resource instanceof TeamModel2
            || $resource instanceof EventParticipantModel
            || $resource instanceof PseudoEventResource
        ) {
            switch ($resource->event->event_type_id) {
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