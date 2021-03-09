<?php

namespace FKSDB\Models;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Services\ServiceTask;
use Nette\Utils\DateTime;

/**
 * Class FKSDB\SeriesCalculator
 */
class SeriesCalculator {

    private ServiceTask $serviceTask;

    public function __construct(ServiceTask $serviceTask) {
        $this->serviceTask = $serviceTask;
    }

    public function getCurrentSeries(ModelContest $contest): int {
        $year = $contest->getCurrentYear();
        $currentSeries = $this->serviceTask->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
            '(submit_deadline < ? OR submit_deadline IS NULL)' => new DateTime(),
        ])->max('series');
        return $currentSeries ?? 1;
    }

    public function getLastSeries(ModelContest $contest, int $year): int {
        return $this->serviceTask->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
        ])->max('series') ?: 1;
    }

    public function getAllowedSeries(ModelContest $contest, int $year): array {
        $lastSeries = $this->getLastSeries($contest, $year);
        return range(1, $lastSeries);
    }

    public function getTotalSeries(ModelContest $contest, int $year): int {
        //TODO allow variance?
        if ($contest->contest_id === ModelContest::ID_VYFUK && $year >= 9) { //TODO Think of better solution of deciding
            return 9;
        } else {
            return 6;
        }
    }
}
