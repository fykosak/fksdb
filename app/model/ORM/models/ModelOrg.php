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
        $data = $this->contest;
        return ModelContest::createFromTableRow($data);
    }

    public function getPerson(): ModelPerson {
        $data = $this->person;
        return ModelPerson::createFromTableRow($data);
    }

    public function getResourceId(): string {
        return 'org';
    }

}
