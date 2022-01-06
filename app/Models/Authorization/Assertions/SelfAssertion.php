<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\ReferencedAccessor;
use Nette\InvalidStateException;
use Nette\Security\IIdentity;
use Nette\Security\Permission;
use Nette\Security\UserStorage;

class SelfAssertion implements Assertion
{

    private UserStorage $userStorage;

    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    /**
     * Check that the person is the person of logged user.
     *
     * @note Grant contest is ignored in this context (i.e. person is context-less).
     */
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        /** @var IIdentity $identity */
        [$state, $identity] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException('Expecting logged user.');
        }
        $model = $acl->getQueriedResource();
        try {
            /** @var ModelContest $contest */
            $contest = ReferencedAccessor::accessModel($model, ModelContest::class);
            if ($contest->contest_id !== $acl->getQueriedRole()->getContest()->contest_id) {
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
        return ($identity->getId() === $person->getLogin()->login_id);
    }
}
