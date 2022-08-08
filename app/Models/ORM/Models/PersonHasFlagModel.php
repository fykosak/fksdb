<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int person_flag_id
 * @property-read int person_id
 * @property-read PersonModel person
 * @property-read int flag_id
 * @property-read FlagModel flag
 * @property-read int contest_id
 * @property-read ContestModel|null contest
 * @property-read int ac_year
 * @property-read int value
 * @property-read \DateTimeInterface modified
 * @property-read \DateTimeInterface created
 */
class PersonHasFlagModel extends Model
{
    public function getContestYear(): ?ContestYearModel
    {
        return $this->contest->getContestYearByAcYear($this->ac_year);
    }
}
