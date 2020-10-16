<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\ORM\Services\ServiceSubmit;

/**
 * Class ContestantsPerSeries
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PerSeriesChart extends AbstractContestantsPerSeriesChart {

    private ServiceSubmit $serviceSubmit;

    public function injectSecondary(ServiceSubmit $serviceSubmit): void {
        $this->serviceSubmit = $serviceSubmit;
    }

    protected function getData(): array {
        $query = $this->serviceSubmit->getTable()
            ->where('task.contest_id', $this->contest->contest_id)
            ->group('task.series, task.year')
            ->select('COUNT(DISTINCT ct_id) AS count,task.series, task.year');
        $data = [];
        foreach ($query as $row) {
            $year = $row->year;
            $series = $row->series;
            $data[$year] = $data[$year] ?? [];
            $data[$year][$series] = $row->count;
        }
        return $data;
    }

    public function getTitle(): string {
        return _('Contestants per series');
    }

    public function getDescription(): ?string {
        return null;
    }
}
