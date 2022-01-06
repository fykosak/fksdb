<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Assertions;

use Nette\Security\Permission;

interface Assertion
{
    public function __invoke(Permission $acl, ?string $role, ?string $resourceId, ?string $privilege): bool;
}
