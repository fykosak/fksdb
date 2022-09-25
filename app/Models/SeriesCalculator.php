<?php

declare(strict_types=1);

namespace FKSDB\Models;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Nette\Utils\DateTime;

class SeriesCalculator
{
    public static function getCurrentSeries(ContestModel $contest): int
    {
        $currentSeries = $contest->getCurrentContestYear()->getTasks()
            ->where('(submit_deadline < ? OR submit_deadline IS NULL)', new DateTime())
            ->max('series');
        return $currentSeries ?? 1;
    }

    public static function getLastSeries(ContestYearModel $contestYear): int
    {
        return $contestYear->getTasks()->max('series') ?? 1;
    }

    public static function getTotalSeries(ContestYearModel $contestYear): int
    {
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
    public static function hasHolidaySeries(ContestYearModel $contestYear): bool
    {
        if ($contestYear->contest_id === ContestModel::ID_VYFUK && $contestYear->year >= 9) {
            return true;
        }
        return false;
    }
}
