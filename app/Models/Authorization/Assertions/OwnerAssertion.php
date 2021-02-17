<?php

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Grant;
use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelSubmit;
use FKSDB\Models\ORM\ReferencedFactory;
use Nette\InvalidStateException;
use Nette\Security\IResource;
use Nette\Security\IUserStorage;
use Nette\Security\Permission;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class OwnerAssertion {

    private IUserStorage $userStorage;

    public function __construct(IUserStorage $userStorage) {
        $this->userStorage = $userStorage;
    }

    /**
     *
     * @param Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     */
    public function isSubmitUploader(Permission $acl, $role, $resourceId, $privilege): bool {
        if (!$this->userStorage->isAuthenticated()) {
            throw new InvalidStateException('Expecting logged user.');
        }
        /** @var ModelSubmit $submit */
        $submit = $acl->getQueriedResource();

        if (!$submit instanceof IResource) {
            return false;
        }
        return $submit->getContestant()->getPerson()->getLogin()->login_id === $this->userStorage->getIdentity()->getId();
    }

    /**
     * Checks whether contestant belongs to the same contest as the role was assigned.
     *
     * @param Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     */
    public function isOwnContestant(Permission $acl, $role, $resourceId, $privilege): bool {
        if (!$this->userStorage->isAuthenticated()) {
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
     *
     * @param Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     */
    public function existsOwnContestant(Permission $acl, $role, $resourceId, $privilege): bool {
        if (!$this->userStorage->isAuthenticated()) {
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
     *
     * @param Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     */
    public function isSelf(Permission $acl, $role, $resourceId, $privilege): bool {
        if (!$this->userStorage->isAuthenticated()) {
            throw new InvalidStateException('Expecting logged user.');
        }

        $loggedPerson = $this->userStorage->getIdentity()->getPerson();
        $model = $acl->getQueriedResource();
        try {
            $contest = ReferencedFactory::accessModel($model, ModelContest::class);
            if ($contest->contest_id !== $acl->getQueriedRole()->getContestId()) {
                return false;
            }
        } catch (CannotAccessModelException $exception) {
        }

        $person = null;
        try {
            $person = ReferencedFactory::accessModel($model, ModelPerson::class);
        } catch (CannotAccessModelException $exception) {
        }

        if (!$person instanceof ModelPerson) {
            return false;
        }
        return ($loggedPerson && $loggedPerson->person_id == $person->person_id);
    }
}
