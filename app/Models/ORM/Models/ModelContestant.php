<?php

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
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
class ModelContestant extends AbstractModelSingle implements IResource {

    public const RESOURCE_ID = 'contestant';

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->contest);
    }

    public function getPersonHistory(): ?ModelPersonHistory {
        /** @var ActiveRow|ModelContestYear $row */
        $row = $this->getContest()->related(DbNames::TAB_CONTEST_YEAR)->where('year', $this->year)->fetch();
        return $this->getPerson()->getHistory($row->ac_year);
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
