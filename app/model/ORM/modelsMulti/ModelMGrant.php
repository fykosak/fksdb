<?php

use Nette\Security\IRole;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMGrant extends AbstractModelMulti implements IRole {

    protected $joiningColumn = 'role_id';

    /**
     * @return string
     */
    public function getRoleId() {
        return $this->getMainModel()->name;
    }

    /**
     * @return ModelContest
     */
    public function getContest() {
        return ModelContest::createFromTableRow($this->getJoinedModel()->ref(DbNames::TAB_CONTEST, 'contest_id'));
    }

}
