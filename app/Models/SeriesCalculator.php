<?php

namespace FKSDB\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use Nette\Utils\DateTime;

/**
 * Class FKSDB\SeriesCalculator
 */
class SeriesCalculator {

    public function getCurrentSeries(ModelContest $contest): int {
        $year = $contest->getCurrentYear();
        $currentSeries = $contest->related(DbNames::TAB_TASK)->where([
            'year' => $year,
            '(submit_deadline < ? OR submit_deadline IS NULL)' => new DateTime(),
        ])->max('series');
        return $currentSeries ?? 1;
    }

    public function getLastSeries(ModelContest $contest, int $year): int {
        return $contest->related(DbNames::TAB_TASK)->where([
            'year' => $year,
        ])->max('series') ?: 1;
    }

    public function getAllowedSeries(ModelContest $contest, int $year): array {
        $lastSeries = $this->getLastSeries($contest, $year);
        $range = range(1, $lastSeries);

        // If the year has holiday series, remove posibility to upload 7th series
        // (due to Astrid's structure)
        if ($this->hasHolidaySeries($contest, $year)) {
            $key = array_search('7', $range);
            unset($range[$key]);
        }
        return $range;
    }

    public function getTotalSeries(ModelContest $contest, int $year): int {
        //TODO Think of better way of getting series count (maybe year schema?)
        if ($this->hasHolidaySeries($contest, $year))
            return 9;

        return 6;
    }

    /**
     * Check if specific year has a holiday series.
     * Made primarly for Výfuk contest.
     */
    public function hasHolidaySeries(ModelContest $contest, int $year): bool {
        if ($contest->contest_id === ModelContest::ID_VYFUK && $year >= 9)
            return true;
        
        return false;
    }
}
