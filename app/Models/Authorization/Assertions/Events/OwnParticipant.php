<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Events;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\Authorization\Roles\Events\ParticipantRole;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Nette\Security\Permission;

class OwnParticipant implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $queriedRole = $acl->getQueriedRole();
        $application = $acl->getQueriedResource();
        if (!$application instanceof EventParticipantModel) {
            throw new WrongAssertionException();
        }
        if ($queriedRole instanceof ParticipantRole) {
            return $queriedRole->getModel()->event_participant_id === $application->event_participant_id;
        }
        return false;
    }
}