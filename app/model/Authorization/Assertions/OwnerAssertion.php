<?php

namespace Authorization\Assertions;

use Nette\InvalidStateException;
use Nette\Security\IResource;
use Nette\Security\Permission;
use Nette\Security\User;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class OwnerAssertion {

    /**
     * @var User
     */
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     *
     * @param \Authorization\Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return boolean
     * @throws InvalidStateException
     */
    public function isSubmitUploader(Permission $acl, $role, $resourceId, $privilege) {
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException('Expecting logged user.');
        }

        $submit = $acl->getQueriedResource();

        if (!$submit instanceof IResource) {
            return false;
        }
        return $submit->getContestant()->getPerson()->getLogin()->login_id === $this->user->getId();
    }

    /**
     * Checks whether contestant belongs to the same contest as the role was assigned.
     *
     * @param \Authorization\Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return boolean
     * @throws InvalidStateException
     */
    public function isOwnContestant(Permission $acl, $role, $resourceId, $privilege) {
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException('Expecting logged user.');
        }

        $contestant = $acl->getQueriedResource();
        $grant = $acl->getQueriedRole();

        return $contestant->contest_id == $grant->getContestId();
    }

    /**
     * Checks whether person is contestant in any of the role-assigned contests.
     *
     * @param \Authorization\Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return boolean
     * @throws InvalidStateException
     */
    public function existsOwnContestant(Permission $acl, $role, $resourceId, $privilege) {
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException('Expecting logged user.');
        }

        $person = $acl->getQueriedResource();
        $grant = $acl->getQueriedRole();

        //TODO restrict also to the current year? Probably another assertion.
        $contestants = $person->getContestants($grant->getContestId());
        return count($contestants) > 0;
    }

    /**
     * Checks the user is the org in queried contest.
     * @param \Nette\Security\Permission $acl
     * @param type $role
     * @param type $resourceId
     * @param type $privilege
     * @return type
     * @throws InvalidStateException
     */
    public function isOrgSelf(Permission $acl, $role, $resourceId, $privilege) {
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException('Expecting logged user.');
        }

        $org = $acl->getQueriedResource();
        $orgLogin = $org->getPerson()->getLogin();
        $grant = $acl->getQueriedRole();

        return ($org->contest_id == $grant->getContestId()) && ($orgLogin->login_id == $this->user->getId());
    }

    /**
     * Check that the person is the person of logged user.
     *
     * @note Grant contest is ignored in this context (i.e. person is context-less).
     *
     * @param \Nette\Security\Permission $acl
     * @param type $role
     * @param type $resourceId
     * @param type $privilege
     * @return type
     * @throws InvalidStateException
     */
    public function isSelf(Permission $acl, $role, $resourceId, $privilege) {
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException('Expecting logged user.');
        }

        $loggedPerson = $this->user->getIdentity()->getPerson();
        $person = $acl->getQueriedResource();

        return ($loggedPerson && $loggedPerson->person_id == $person->person_id);
    }

}
