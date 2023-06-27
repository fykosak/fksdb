<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Security\Role;

/**
 * POD for briefer encapsulation of granted roles (instead of ModelMGrant).
 */
class Grant implements Role
{
    private ?ContestModel $contest;
    private string $roleId;

    public function __construct(string $roleId, ?ContestModel $contest = null)
    {
        $this->roleId = $roleId;
        $this->contest = $contest;
    }

    public function getContest(): ?ContestModel
    {
        return $this->contest;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }
}
