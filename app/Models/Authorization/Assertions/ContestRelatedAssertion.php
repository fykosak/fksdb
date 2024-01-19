<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\ContestRole;
use FKSDB\Models\Authorization\ContestYearRole;
use FKSDB\Models\Authorization\EventRole\EventRole;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\UserStorage;

class ContestRelatedAssertion implements Assertion
{
    private UserStorage $userStorage;

    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    /**
     * Checks whether person is contestant in any of the role-assigned contests.
     * @throws BadTypeException
     */
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        [$state] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException('Expecting logged user.');
        }
        $person = $acl->getQueriedResource();
        if (!$person instanceof PersonModel) {
            throw new BadTypeException(PersonModel::class, $person);
        }
        $role = $acl->getQueriedRole();
        $contest = null;
        if ($role instanceof ContestRole) {
            $contest = $role->getContest();
        } elseif ($role instanceof ContestYearRole) {
            $contest = $role->getContestYear()->contest;
        } elseif ($role instanceof EventRole) {
            $contest = $role->getEvent()->event_type->contest;
        }

        if (!$contest) {
            return false;
        }
        if ($person->getContestants($contest)->fetch()) {
            return true;
        }
        if ($person->getOrganizer($contest)) {
            return true;
        }
        // TODO events
        return false;
    }
}
