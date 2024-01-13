<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use Nette\Security\Role;

final class BaseRole implements Role
{
    // phpcs:disable
    public const Registered = 'registered';
    public const Guest = 'guest';
    // phpcs:enable

    private string $roleId;

    public function __construct(string $roleId)
    {
        $this->roleId = $roleId;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }
}
