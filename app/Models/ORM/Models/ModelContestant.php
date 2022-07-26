<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\DbNames;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;

/**
 * @property-read ModelPerson person
 * @property-read int person_id
 * @property-read ModelContest contest
 * @property-read int ct_id
 * @property-read int contest_id
 * @property-read int year
 */
class ModelContestant extends Model implements Resource
{
    public const RESOURCE_ID = 'contestant';

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person, $this->mapper);
    }

    public function getContestYear(): ModelContestYear
    {
        $row = $this->getTable()->createSelectionInstance(DbNames::TAB_CONTEST_YEAR)->where(
            'contest_id',
            $this->contest_id
        )->where('year', $this->year)->fetch();
        return ModelContestYear::createFromActiveRow($row, $this->mapper);
    }

    public function getPersonHistory(): ModelPersonHistory
    {
        return $this->person->getHistoryByContestYear($this->getContestYear());
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
