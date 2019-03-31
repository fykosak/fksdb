<?php

namespace Authorization;

use Nette\Security\IRole;

/**
 * POD for briefer encapsulation of granted roles (instead of ModelMGrant).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Grant implements IRole {

    const CONTEST_ALL = -1;

    /**
     * @var int
     */
    private $contestId;

    /**
     * @var string
     */
    private $roleId;

    /**
     * Grant constructor.
     * @param int $contestId
     * @param string $roleId
     */
    function __construct(int $contestId, string $roleId) {
        $this->contestId = $contestId;
        $this->roleId = $roleId;
    }

    /**
     * @return int
     */
    public function getContestId(): int {
        return $this->contestId;
    }

    /**
     * @return string
     */
    public function getRoleId(): string {
        return $this->roleId;
    }

}
