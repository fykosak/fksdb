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
 * @property int since
 * @property int contest_id
 * @property int|null until
 */
class ModelOrg extends AbstractModelSingle implements IResource {

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        return ModelContest::createFromTableRow($this->contest);
    }

    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'org';
    }

}
