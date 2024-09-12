<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use Nette\Security\Permission;

interface Assertion
{
    public function __invoke(Permission $acl): bool;
}
