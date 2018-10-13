<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property ActiveRow contest
 * @property ActiveRow person
 */
class ModelOrg extends AbstractModelSingle implements IResource {

    public function getContest(): ModelContest {
        return ModelContest::createFromTableRow($this->contest);
    }

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    public function getResourceId(): string {
        return 'org';
    }

}
