<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\PseudoEventResource;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\InvalidStateException;
use Nette\Security\Permission;

class IsRegistrationOpened implements Assertion
{

    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $resource = $acl->getQueriedResource();
        if (
            $resource instanceof TeamModel2
            || $resource instanceof EventParticipantModel
            || $resource instanceof PseudoEventResource
        ) {
            $event = $resource->event;
        } else {
            throw new InvalidStateException();
        }
        return $event->isRegistrationOpened();
    }
}
