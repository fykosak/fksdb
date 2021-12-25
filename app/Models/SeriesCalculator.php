<?php

namespace FKSDB\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelContestYear;
use Nette\Utils\DateTime;

class SeriesCalculator {

    public static function getCurrentSeries(ModelContest $contest): int {
        $year = $contest->getCurrentContestYear()->year;
        $currentSeries = $contest->related(DbNames::TAB_TASK)->where([
            'year' => $year,
            '(submit_deadline < ? OR submit_deadline IS NULL)' => new DateTime(),
        ])->max('series');
        return $currentSeries ?? 1;
    }

    public static function getLastSeries(ModelContestYear $contestYear): int {
        return $contestYear->getContest()->related(DbNames::TAB_TASK)->where([
            'year' => $contestYear->year,
        ])->max('series') ?? 1;
    }

    public static function getTotalSeries(ModelContestYear $contestYear): int {
        //TODO Think of better way of getting series count (maybe year schema?)
        if (static::hasHolidaySeries($contestYear)) {
            return 9;
        }
        return 6;
    }

    /**
     * Check if specific year has a holiday series.
     * Made primarly for Výfuk contest.
     */
    public static function hasHolidaySeries(ModelContestYear $contestYear): bool {
        if ($contestYear->contest_id === ModelContest::ID_VYFUK && $contestYear->year >= 9) {
            return true;
        }
        return false;
    }
}
