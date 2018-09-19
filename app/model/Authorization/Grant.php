<?php

namespace Authorization;

use Nette\Security\IRole;

/**
 * POD for briefer encapsulation of granted roles (instead of ModelMGrant).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Grant implements IRole {

    /**
     * @var int
     */
    private $contestId;

    /**
     * @var string
     */
    private $roleId;

    function __construct($contestId, $roleId) {
        $this->contestId = $contestId;
        $this->roleId = $roleId;
    }

    public function getContestId() {
        return $this->contestId;
    }

    public function getRoleId() {
        return $this->roleId;
    }

}
