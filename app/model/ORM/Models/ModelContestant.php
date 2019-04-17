<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property ActiveRow person
 * @property ActiveRow contest
 * @property int ct_id
 * @property int contest_id
 * @property int year
 */
class ModelContestant extends AbstractModelSingle implements IResource, IPersonReferencedModel, IContestReferencedModel {
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
