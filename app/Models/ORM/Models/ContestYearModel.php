<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;

/**
 * @property-read int contest_id
 * @property-read int ac_year
 * @property-read int year
 * @property-read ContestModel contest
 */
class ContestYearModel extends Model
{
    public function getContestants(): TypedGroupedSelection
    {
        return $this->contest->related(DbNames::TAB_CONTESTANT)->where('year', $this->year);
    }

    public function getTasks(?int $series = null): TypedGroupedSelection
    {
        $query = $this->contest->getTasks()->where('year', $this->year);

        if (isset($series)) {
            $query->where('series', $series);
        }
        return $query;
    }
}
