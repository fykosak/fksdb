<?php

namespace FKSDB\ORM\ModelsMulti;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use Nette\Security\IRole;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMGrant extends AbstractModelMulti implements IRole {

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
        return ModelContest::createFromActiveRow($this->getJoinedModel()->ref(DbNames::TAB_CONTEST, 'contest_id'));
    }

}
