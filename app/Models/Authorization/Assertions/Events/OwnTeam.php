<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Events;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamMemberRole;
use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamTeacherRole;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Security\Permission;

class OwnTeam implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $queriedRole = $acl->getQueriedRole();
        $holder = $acl->getQueriedResource();
        $application = $holder->getResource();
        if (!$application instanceof TeamModel2) {
            throw new WrongAssertionException();
        }
        if ($queriedRole instanceof TeamTeacherRole) {
            if ($queriedRole->getModel()->fyziklani_team_id === $application->fyziklani_team_id) {
                return true;
            }
            return false;
        }
        if ($queriedRole instanceof TeamMemberRole) {
            if ($queriedRole->getModel()->fyziklani_team_id === $application->fyziklani_team_id) {
                return true;
            }
            return false;
        }
        return false;
    }
}
