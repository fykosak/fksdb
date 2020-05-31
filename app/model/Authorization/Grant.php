<?php

namespace Authorization;

use Nette\Security\IRole;

/**
 * POD for briefer encapsulation of granted roles (instead of ModelMGrant).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Grant implements IRole {

    public const CONTEST_ALL = -1;

    private int $contestId;

    private string $roleId;

    /**
     * Grant constructor.
     * @param int $contestId
     * @param string $roleId
     */
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
