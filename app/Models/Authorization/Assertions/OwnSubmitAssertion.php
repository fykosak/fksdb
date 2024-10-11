<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use FKSDB\Models\Authorization\Roles\ContestYear\ContestantRole;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SubmitModel;
use Nette\Security\Permission;

class OwnSubmitAssertion implements Assertion
{
    /**
     * @throws BadTypeException
     */
    public function __invoke(Permission $acl): bool
    {
        $role = $acl->getQueriedRole();
        if (!$role instanceof ContestantRole) {
            return false;
        }
        $holder = $acl->getQueriedResource();
        $submit = $holder->getResource();
        if ($submit instanceof SubmitModel) {
            return $submit->contestant->contestant_id === $role->contestant->contestant_id;
        }
        if ($submit === SubmitModel::RESOURCE_ID) {
            return false;
        }
        throw new WrongAssertionException();
    }
}
