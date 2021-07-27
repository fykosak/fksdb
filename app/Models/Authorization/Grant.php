<?php

namespace FKSDB\Models\Authorization;

use Nette\Security\Role;

/**
 * POD for briefer encapsulation of granted roles (instead of ModelMGrant).
 */
class Grant implements Role {

    public const CONTEST_ALL = -1;

    private int $contestId;

    private string $roleId;

    public function __construct(int $contestId, string $roleId) {
        $this->contestId = $contestId;
        $this->roleId = $roleId;
    }

    public function getContestId(): int {
        return $this->contestId;
    }

    public function getRoleId(): string {
        return $this->roleId;
    }
}
