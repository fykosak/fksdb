<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Events;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use Nette\Security\Permission;

class OwnTeamApplication implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $queriedRole = $acl->getQueriedRole();
        $holder = $acl->getQueriedResource();
        $application = $holder->getResource();
        if (!$application instanceof TeamModel2) {
            throw new WrongAssertionException();
        }
        if ($queriedRole instanceof TeamTeacherModel) {
            if ($queriedRole->fyziklani_team_id === $application->fyziklani_team_id) {
                return true;
            }
            return false;
        }
        if ($queriedRole instanceof TeamMemberModel) {
            if ($queriedRole->fyziklani_team_id === $application->fyziklani_team_id) {
                return true;
            }
            return false;
        }
        return false;
    }
}
