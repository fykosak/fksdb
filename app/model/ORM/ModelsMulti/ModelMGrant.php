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

    public function getRoleId(): string {
        return $this->getMainModel()->name;
    }

    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->getJoinedModel()->ref(DbNames::TAB_CONTEST, 'contest_id'));
    }

}
