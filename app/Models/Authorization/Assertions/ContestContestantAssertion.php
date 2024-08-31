<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\ContestRole;
use FKSDB\Models\Authorization\Roles\ContestYearRole;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestantModel;
use Nette\Security\Permission;

class ContestContestantAssertion implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $contestant = $acl->getQueriedResource();
        if (!$contestant instanceof ContestantModel) {
            throw new WrongAssertionException();
        }
        $grant = $acl->getQueriedRole();
        if ($grant instanceof ContestRole) {
            return $contestant->contest_id === $grant->getContest()->contest_id;
        }
        if ($grant instanceof ContestYearRole) {
            return $contestant->contest_id === $grant->getContestYear()->contest_id
                && $contestant->year === $grant->getContestYear()->year;
        }
        return false;
    }
}
