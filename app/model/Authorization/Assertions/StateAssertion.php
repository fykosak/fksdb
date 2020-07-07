<?php

namespace Authorization\Assertions;

use Authorization\Grant;
use FKSDB\ORM\Models\IContestReferencedModel;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\Transitions\Machine;
use Nette\InvalidStateException;
use Nette\Security\IResource;
use Nette\Security\IUserStorage;
use Nette\Security\Permission;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StateAssertion {

    /**
     * @var IUserStorage
     */
    private $user;

    /**
     * OwnerAssertion constructor.
     * @param IUserStorage $user
     */
    public function __construct(IUserStorage $user) {
        $this->user = $user;
    }

    /**
     *
     * @param Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     * @throws InvalidStateException
     */
    public function isPaymentEditable(Permission $acl, $role, $resourceId, $privilege): bool {

        if (!$this->user->isAuthenticated()) {
            throw new InvalidStateException('Expecting logged user.');
        }
        /** @var ModelPayment $payment */
        $payment = $acl->getQueriedResource();
        return \in_array($payment->getState(), [Machine::STATE_INIT, ModelPayment::STATE_NEW]);
    }

    public function isPaymentEffaceable(Permission $acl, $role, $resourceId, $privilege): bool {

    }

    /**
     * Checks whether contestant belongs to the same contest as the role was assigned.
     *
     * @param Permission $acl
     * @param string $role
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     * @throws InvalidStateException
     */
    public function isOwnContestant(Permission $acl, $role, $resourceId, $privilege): bool {
        if (!$this->user->isAuthenticated()) {
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
     * @throws InvalidStateException
     */
    public function existsOwnContestant(Permission $acl, $role, $resourceId, $privilege): bool {
        if (!$this->user->isAuthenticated()) {
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
     * @throws InvalidStateException
     */
    public function isSelf(Permission $acl, $role, $resourceId, $privilege): bool {
        if (!$this->user->isAuthenticated()) {
            throw new InvalidStateException('Expecting logged user.');
        }

        $loggedPerson = $this->user->getIdentity()->getPerson();
        $model = $acl->getQueriedResource();
        if ($model instanceof IContestReferencedModel) {
            if ($model->getContest()->contest_id !== $acl->getQueriedRole()->getContestId()) {
                return false;
            }
        }
        if ($model instanceof IPersonReferencedModel) {
            $model = $model->getPerson();
        }

        if (!$model instanceof ModelPerson) {
            return false;
        }
        return ($loggedPerson && $loggedPerson->person_id == $model->person_id);
    }
}
