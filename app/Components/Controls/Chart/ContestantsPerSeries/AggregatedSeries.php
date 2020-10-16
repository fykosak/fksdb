<?php

namespace FKSDB\Components\Controls\Chart;

use Nette\Database\Context;

/**
 * Class ContestantsPerSeries
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AggregatedSeries extends AbstractContestantsPerSeriesChart {

    private Context $context;

    public function injectSecondary(Context $context): void {
        $this->context = $context;
    }

    protected function getData(): array {
        $query = $this->context->query('select ts.year,
       ts.series,
       (
           select COUNT(DISTINCT ct_id) as count
           from submit s
                    join task t on t.task_id = s.task_id
           where ts.series >= t.series
             AND ts.year = t.year
       ) as \'count\'
from task ts
where contest_id = ?
group by year, series', $this->contest->contest_id);
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
        return _('Total contestants per series');
    }

    public function getDescription(): ?string {
        return null;
    }
}
