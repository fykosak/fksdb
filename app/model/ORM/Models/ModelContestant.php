<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow person
 * @property-read int person_id
 * @property-read ActiveRow contest
 * @property-read int ct_id
 * @property-read int contest_id
 * @property-read int year
 */
class ModelContestant extends AbstractModelSingle implements IResource, IPersonReferencedModel, IContestReferencedModel {
    const RESOURCE_ID = 'contestant';

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->contest);
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
