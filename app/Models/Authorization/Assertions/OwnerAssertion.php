<?php

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Grant;
use FKSDB\Models\Events\Semantics\Role;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelSubmit;
use FKSDB\Models\ORM\ReferencedAccessor;
use Nette\InvalidStateException;
use Nette\Security\IIdentity;
use Nette\Security\Resource;
use Nette\Security\Permission;
use Nette\Security\UserStorage;

class OwnerAssertion
{

    private UserStorage $userStorage;

    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    /**
     * @param string|Role $role
     * @param string|Resource $resourceId
     */
    public function isSubmitUploader(Permission $acl, $role, $resourceId, ?string $privilege): bool
    {
        [, $login] = $this->userStorage->getState();
        if (!$login) {
            throw new InvalidStateException('Expecting logged user.');
        }
        /** @var ModelSubmit $submit */
        $submit = $acl->getQueriedResource();

        if (!$submit instanceof Resource) {
            return false;
        }
        return $submit->getContestant()->getPerson()->getLogin()->login_id === $login->getId();
    }

    /**
     * Checks whether contestant belongs to the same contest as the role was assigned.
     * @param string|Role $role
     * @param string|Resource $resourceId
     */
    public function isOwnContestant(Permission $acl, $role, $resourceId, ?string $privilege): bool
    {
        [$state] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException('Expecting logged user.');
        }
        /** @var ModelContestant $contestant */
        $contestant = $acl->getQueriedResource();
        /** @var Grant $grant */
        $grant = $acl->getQueriedRole();

        return $contestant->contest_id == $grant->getContestId();
    }

    /**
     * Checks whether person is contestant in any of the role-assigned contests.
     * @param string|Role $role
     * @param string|Resource $resourceId
     */
    public function existsOwnContestant(Permission $acl, $role, $resourceId, ?string $privilege): bool
    {
        [$state] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException('Expecting logged user.');
        }
        /** @var ModelPerson $person */
        $person = $acl->getQueriedResource();
        /** @var Grant $grant */
        $grant = $acl->getQueriedRole();

        //TODO restrict also to the current year? Probably another assertion.
        $contestants = $person->getContestants($grant->getContestId());
        return count($contestants) > 0;
    }

    /**
     * Check that the person is the person of logged user.
     *
     * @note Grant contest is ignored in this context (i.e. person is context-less).
     * @param string|Role $role
     * @param string|Resource $resourceId
     */
    public function isSelf(Permission $acl, $role, $resourceId, ?string $privilege): bool
    {
        /** @var IIdentity $login */
        [$state, $login] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException('Expecting logged user.');
        }
        $model = $acl->getQueriedResource();
        try {
            /** @var ModelContest $contest */
            $contest = ReferencedAccessor::accessModel($model, ModelContest::class);
            if ($contest->contest_id !== $acl->getQueriedRole()->getContestId()) {
                return false;
            }
        } catch (CannotAccessModelException $exception) {
        }

        $person = null;
        try {
            $person = ReferencedAccessor::accessModel($model, ModelPerson::class);
        } catch (CannotAccessModelException $exception) {
        }

        if (!$person instanceof ModelPerson) {
            return false;
        }
        return ($login->getId() == $person->getLogin()->login_id);
    }
}
