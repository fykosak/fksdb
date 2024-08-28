<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\Events\ParticipantRole;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Nette\Security\Permission;

class OwnApplicationAssertion implements Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $queriedRole = $acl->getQueriedRole();
        $application = $acl->getQueriedResource();
        if ($application instanceof EventParticipantModel && $queriedRole instanceof ParticipantRole) {
            return $queriedRole->eventParticipant->event_participant_id === $application->event_participant_id;
        }
        return false;
    }
}
