<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Utils\DateTime;

/**
 * @property-read int $person_flag_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $flag_id
 * @property-read FlagModel $flag
 * @property-read int|null $contest_id
 * @property-read ContestModel|null $contest
 * @property-read int|null $ac_year
 * @property-read int $value
 * @property-read DateTime $modified
 * @property-read DateTime $created
 */
final class PersonHasFlagModel extends Model
{
    public function getContestYear(): ?ContestYearModel
    {
        return $this->contest->getContestYearByAcYear($this->ac_year);
    }
}
