<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Nette\Security\Role;

class ContestYearRole implements Role
{
    private ContestYearModel $contestYear;
    private string $roleId;

    public function __construct(string $roleId, ContestYearModel $contestYear)
    {
        $this->roleId = $roleId;
        $this->contestYear = $contestYear;
    }

    public function getContestYear(): ContestYearModel
    {
        return $this->contestYear;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }
}
