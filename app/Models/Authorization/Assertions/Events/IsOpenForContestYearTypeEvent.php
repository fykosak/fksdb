<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions\Events;

use FKSDB\Models\Authorization\Assertions\Assertion;
use FKSDB\Models\Authorization\Assertions\WrongAssertionException;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Authorization\Roles\ContestYear\ContestantRole;
use Nette\Security\Permission;

class IsOpenForContestYearTypeEvent implements Assertion
{
    public function __invoke(Permission $acl): bool
    {
        $role = $acl->getQueriedRole();
        if (!$role instanceof ContestantRole) {
            return false;
        }

        $holder = $acl->getQueriedResource();
        if (!$holder instanceof EventResourceHolder) {
            throw new WrongAssertionException();
        }

        $contestantContestYear = $role->getContestYear();
        $eventContestYear = $holder->getContext()->getContestYear();

        return $contestantContestYear->contest_id === $eventContestYear->contest_id
            && $contestantContestYear->year === $eventContestYear->year
            && $holder->getContext()->event_type->isOpenForContestYearType();
    }
}
