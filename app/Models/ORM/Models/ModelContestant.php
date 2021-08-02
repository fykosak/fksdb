<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;

/**
 * @property-read ActiveRow person
 * @property-read int person_id
 * @property-read ActiveRow contest
 * @property-read int ct_id
 * @property-read int contest_id
 * @property-read int year
 */
class ModelContestant extends AbstractModel implements Resource
{
    public const RESOURCE_ID = 'contestant';

    public function getContest(): ModelContest
    {
        return ModelContest::createFromActiveRow($this->contest);
    }

    public function getPersonHistory(): ModelPersonHistory
    {
        return $this->getPerson()->getHistoryByContestYear($this->getContestYear());
    }

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getContestYear(): ModelContestYear
    {
        $row = $this->getTable()->createSelectionInstance(DbNames::TAB_CONTEST_YEAR)->where(
            'contest_id',
            $this->contest_id
        )->where('year', $this->year)->fetch();
        return ModelContestYear::createFromActiveRow($row);
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
