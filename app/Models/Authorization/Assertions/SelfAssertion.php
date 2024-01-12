<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\EventRole\EventRole;
use FKSDB\Models\Authorization\ContestRole;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
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
     * @throws \ReflectionException
     */
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        /** @var IIdentity $identity */
        [$state, $identity] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException(_('Expecting logged user.'));
        }
        /** @var Model $model */
        $model = $acl->getQueriedResource();
        try {
            $grant = $acl->getQueriedRole();
            $contest = $model->getReferencedModel(ContestModel::class);
            if ($grant instanceof ContestRole) {
                if ($contest->contest_id !== $grant->getContest()->contest_id) {
                    return false;
                }
            } elseif ($grant instanceof EventRole) {
                if ($contest->contest_id !== $grant->getEvent()->event_type->contest_id) {
                    return false;
                }
            }
        } catch (CannotAccessModelException $exception) {
        }

        $person = null;
        try {
            $person = $model->getReferencedModel(PersonModel::class);
        } catch (CannotAccessModelException $exception) {
        }

        if (!$person instanceof PersonModel) {
            return false;
        }
        return ($identity->getId() === $person->getLogin()->login_id);
    }
}
