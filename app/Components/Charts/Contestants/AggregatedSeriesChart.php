<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Charts\Contestants;

use Nette\Database\Explorer;

class AggregatedSeriesChart extends AbstractPerSeriesChart
{

    private Explorer $explorer;

    public function injectSecondary(Explorer $explorer): void
    {
        $this->explorer = $explorer;
    }

    protected function getData(): array
    {
        $query = $this->explorer->query(
            'select ts.year,
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
group by year, series',
            $this->contest->contest_id
        );
        $data = [];
        foreach ($query as $row) {
            /** @var int $year */
            $year = $row->year;
            $series = $row->series;
            $data[$year] = $data[$year] ?? [];
            $data[$year][$series] = $row->count;
        }
        return $data;
    }

    public function getTitle(): string
    {
        return _('Total contestants per series');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
