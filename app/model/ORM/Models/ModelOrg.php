<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readActiveRow contest
 * @property-readActiveRow person
 * @property-readint since
 * @property-readint contest_id
 * @property-readint|null until
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
