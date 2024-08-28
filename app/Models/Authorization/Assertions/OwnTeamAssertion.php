<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamMemberRole;
use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamTeacherRole;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Nette\Security\Permission;

class OwnTeamAssertion implements Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        $queriedRole = $acl->getQueriedRole();
        $application = $acl->getQueriedResource();
        if ($application instanceof TeamModel2 && $queriedRole instanceof TeamTeacherRole) {
            foreach ($queriedRole->teams as $team) {
                if (
                    $team->fyziklani_team_id === $application->fyziklani_team_id
                    && $application->state->value !== TeamState::Disqualified
                ) {
                    return true;
                }
            }
            return false;
        }
        if ($application instanceof TeamModel2 && $queriedRole instanceof TeamMemberRole) {
            if (
                $queriedRole->member->fyziklani_team_id === $application->fyziklani_team_id
                && $application->state->value !== TeamState::Disqualified
            ) {
                return true;
            }
            return false;
        }
        return false;
    }
}