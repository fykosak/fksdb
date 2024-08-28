<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamMemberRole;
use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamTeacherRole;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Security\Permission;

class OwnTeamAssertion implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $queriedRole = $acl->getQueriedRole();
        $application = $acl->getQueriedResource();
        if (!$application instanceof TeamModel2) {
            throw new WrongAssertionException();
        }
        if ($queriedRole instanceof TeamTeacherRole) {
            foreach ($queriedRole->teams as $team) {
                if (
                    $team->fyziklani_team_id === $application->fyziklani_team_id
                ) {
                    return true;
                }
            }
            return false;
        }
        if ($queriedRole instanceof TeamMemberRole) {
            if (
                $queriedRole->member->fyziklani_team_id === $application->fyziklani_team_id
            ) {
                return true;
            }
            return false;
        }
        return false;
    }
}