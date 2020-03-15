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
class ModelContestant extends AbstractModelSingle implements IResource, IPersonReferencedModel, IContestReferencedModel {
    const RESOURCE_ID = 'contestant';

    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->contest);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }

}
