<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SubmitModel;
use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\UserStorage;

class OwnSubmitAssertion implements Assertion
{
    private UserStorage $userStorage;

    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    /**
     * @throws BadTypeException
     */
    public function __invoke(Permission $acl): bool
    {
        [$state, $login] = $this->userStorage->getState();
        if (!$state) {
            throw new InvalidStateException('Expecting logged user.');
        }
        $submit = $acl->getQueriedResource();
        if (!$submit instanceof SubmitModel) {
            throw new BadTypeException(SubmitModel::class, $submit);
        }
        return $submit->contestant->person->getLogin()->getId() === $login->getId();
    }
}
