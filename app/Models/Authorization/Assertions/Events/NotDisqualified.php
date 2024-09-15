<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Events;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Nette\Security\Permission;

class NotDisqualified implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $application = $acl->getQueriedResource();
        if ($application instanceof TeamModel2) {
            return $application->state->value !== TeamState::Disqualified;
        }
        if ($application instanceof EventParticipantModel) {
            return $application->status->value !== EventParticipantStatus::DISQUALIFIED;
        }
        throw new WrongAssertionException();
    }
}
