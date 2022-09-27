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
        return $this->contest->related(DbNames::TAB_CONTESTANT, 'contest_id')->where('year', $this->year);
    }

    public function getTasks(?int $series = null): TypedGroupedSelection
    {
        $query = $this->contest->getTasks()->where('year', $this->year);
        if (isset($series)) {
            $query->where('series', $series);
        }
        return $query;
    }

    public function getLastSeries(): int
    {
        return $this->getTasks()->max('series') ?? 1;
    }

    public function getTotalSeries(): int
    {
        return $this->hasHolidaySeries() ? 9 : 6;
    }

    /**
     * Check if specific year has a holiday series.
     * Made primarly for Výfuk contest.
     */
    public function hasHolidaySeries(): bool
    {
        return $this->contest_id === ContestModel::ID_VYFUK && $this->year >= 9;
    }

    public function getGraduationYear(int $studyYear): int
    {
        if ($studyYear >= 6 && $studyYear <= 9) {
            return $this->ac_year + (5 - ($studyYear - 9));
        }
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $this->ac_year + (5 - $studyYear);
        }
        throw new \InvalidArgumentException('Graduation year not match');
    }
}
