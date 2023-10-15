<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Contestants;

use Fykosak\Utils\UI\Title;
use Nette\Database\Explorer;
use Nette\Database\Row;

class AggregatedSeriesChart extends AbstractPerSeriesChart
{

    private Explorer $explorer;

    public function injectSecondary(Explorer $explorer): void
    {
        $this->explorer = $explorer;
    }

    /**
     * @phpstan-return array<int,array<int,int>>
     */
    protected function getData(): array
    {
        $query = $this->explorer->query(
            'select * from (
                select ts.year, ts.series,
                (
                   select COUNT(DISTINCT contestant_id) as count
                   from submit s
                            join task t on t.task_id = s.task_id
                   where ts.series >= t.series
                     AND ts.year = t.year
                   AND ts.contest_id = t.contest_id
                ) as \'count\'
            from task ts
            where contest_id = ?
            group by year, series
            ) tsc
            where tsc.count != 0',
            $this->contest->contest_id
        );
        $data = [];
        /** @var Row $row */
        foreach ($query as $row) {
            $year = (int)$row->year;
            $series = (int)$row->series;
            $data[$year] = $data[$year] ?? [];
            $data[$year][$series] = (int)$row->count;
        }
        return $data;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Total contestants per series'), 'fas fa-chart-column');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
