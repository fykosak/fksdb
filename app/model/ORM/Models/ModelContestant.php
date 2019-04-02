<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow person
 * @property-read ActiveRow contest
 * @property-read int ct_id
 * @property-read int contest_id
 * @property-read int year
 */
class ModelContestant extends AbstractModelSingle implements IResource {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        $data = $this->person;
        return ModelPerson::createFromActiveRow($data);
    }

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        $data = $this->contest;
        return ModelContest::createFromActiveRow($data);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'contestant';
    }

}
