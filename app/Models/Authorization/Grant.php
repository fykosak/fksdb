<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\ORM\Models\ModelContest;
use Nette\Security\Role;

/**
 * POD for briefer encapsulation of granted roles (instead of ModelMGrant).
 */
class Grant implements Role
{

    public const CONTEST_ALL = -1;

    private int $contestId;
    private ?ModelContest $contest;

    private string $roleId;

    public function __construct(int $contestId, string $roleId, ?ModelContest $contest = null)
    {
        $this->contestId = $contestId;
        $this->roleId = $roleId;
        $this->contest = $contest;
    }

    public function getContestId(): int
    {
        return isset($this->contest) ? $this->contest->contest_id : $this->contestId;
    }

    public function getContest(): ModelContest
    {
        return $this->contest;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }
}
