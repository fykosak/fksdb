<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use Nette\Security\Resource;

/**
 * @property-read int ct_id
 * @property-read int contest_id
 * @property-read ContestModel contest
 * @property-read int year
 * @property-read int person_id
 * @property-read PersonModel person
 * @property-read \DateTimeInterface created
 */
class ContestantModel extends Model implements Resource
{
    public const RESOURCE_ID = 'contestant';

    public function getContestYear(): ContestYearModel
    {
        return $this->contest->getContestYear($this->year);
    }

    public function getPersonHistory(): PersonHistoryModel
    {
        return $this->person->getHistoryByContestYear($this->getContestYear());
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
