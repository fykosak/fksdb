<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Grant;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\UserStorage;

class OwnerAssertion
{

    private UserStorage $userStorage;

    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    public function isSubmitUploader(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        [, $login] = $this->userStorage->getState();
        if (!$login) {
            throw new InvalidStateException(_('Expecting logged user.'));
        }
        /** @var SubmitModel $submit */
        $submit = $acl->getQueriedResource();

        if (!$submit instanceof Resource) {
            return false;
        }
        return $submit->contestant->person->getLogin()->login_id === $login->getId();
    }

    /**
     * Checks whether contestant belongs to the same contest as the role was assigned.
     */
    public function isOwnContestant(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        [$state] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException(_('Expecting logged user.'));
        }
        /** @var ContestantModel $contestant */
        $contestant = $acl->getQueriedResource();
        /** @var Grant $grant */
        $grant = $acl->getQueriedRole();

        return $contestant->contest_id === $grant->getContest()->contest_id;
    }

    /**
     * Checks whether person is contestant in any of the role-assigned contests.
     */
    public function existsOwnContestant(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        [$state] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException(_('Expecting logged user.'));
        }
        /** @var PersonModel $person */
        $person = $acl->getQueriedResource();
        /** @var Grant $grant */
        $grant = $acl->getQueriedRole();

        //TODO restrict also to the current year? Probably another assertion.
        return $person->getContestants($grant->getContest())->count('*') > 0;
    }
}
