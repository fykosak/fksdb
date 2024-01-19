<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\ORM\Models\SubmitModel;
use Nette\Security\Permission;
use Nette\Security\UserStorage;

class OwnSubmitAssertion implements Assertion
{
    private UserStorage $userStorage;

    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool
    {
        [$state, $login] = $this->userStorage->getState();
        if (!$state) {
            return false;
        }
        $submit = $acl->getQueriedResource();
        if (!$submit instanceof SubmitModel) {
            return false;
        }
        return $submit->contestant->person->getLogin()->getId() === $login->getId();
    }
}
