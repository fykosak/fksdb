<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\Contest\ContestRole;
use FKSDB\Models\Authorization\Roles\ContestYear\ContestYearRole;
use FKSDB\Models\ORM\Models\ContestantModel;
use Nette\Security\Permission;

class ContestContestantAssertion implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $holder = $acl->getQueriedResource();
        $contestant = $holder->getResource();
        if (!$contestant instanceof ContestantModel) {
            throw new WrongAssertionException();
        }
        $grant = $acl->getQueriedRole();
        if ($grant instanceof ContestYearRole) {
            return $contestant->contest_id === $grant->getContestYear()->contest_id
                && $contestant->year === $grant->getContestYear()->year;
        }
        if ($grant instanceof ContestRole) {
            return $contestant->contest_id === $grant->getContest()->contest_id;
        }
        return false;
    }
}
