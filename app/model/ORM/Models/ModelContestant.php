<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readActiveRow person
 * @property-readActiveRow contest
 * @property-readint ct_id
 * @property-readint contest_id
 * @property-readint year
 */
class ModelContestant extends AbstractModelSingle implements IResource {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        $data = $this->person;
        return ModelPerson::createFromTableRow($data);
    }

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        $data = $this->contest;
        return ModelContest::createFromTableRow($data);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'contestant';
    }

}
